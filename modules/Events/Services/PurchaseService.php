<?php


namespace modules\Events\Services;


use craft\base\Component;
use craft\commerce\base\Purchasable;
use craft\commerce\elements\Order;
use craft\commerce\models\LineItem;
use craft\commerce\Plugin as Commerce;
use modules\Events\Errors\ConfirmError;
use modules\Events\Errors\HoldError;
use modules\Events\Errors\SoldOutError;
use \modules\Events\Models\ScheduleModel;
use modules\Rezdy\RezdyModule;
use modules\Rezdy\Services\RezdyApiService;
use yii\db\Expression;
use yii\db\Query;
use \Craft;

/**
 *
 * @property void $scheduleById
 * @property null|array $allSchedules
 */
class PurchaseService extends Component {

    /**
     * Cancel all event orders on this order.
     * @param Order $order
     *
     * @return Order
     * @throws \Throwable
     */
	public function cancel( Order $order ): Order {
		$eventApiLineItems = $this->getEventLineItems( $order );
		if ( count( $eventApiLineItems ) ) {
			foreach ( $eventApiLineItems as $eventApiLineItem ) {
				$response = RezdyModule::getInstance()->api->cancelBooking( $eventApiLineItem, $order );
                if ( $response->isSuccessful() ) {
					$currentOptions = $eventApiLineItem->getOptions();
					$lineItemChanged = false;
					if(isset($currentOptions['apiBookingId'] )){
						unset( $currentOptions['apiBookingId'] );
						$eventApiLineItem->setOptions( $currentOptions );
						$lineItemChanged = true;
					}
                    if(isset($currentOptions['apiCompleteBookingId'] )){
                        unset( $currentOptions['apiCompleteBookingId'] );
                        $eventApiLineItem->setOptions( $currentOptions );
                        $lineItemChanged = true;
                    }
                    if($lineItemChanged){
                        Commerce::getInstance()->lineItems->saveLineItem($eventApiLineItem, false);
                    }
				}
			}
		}
		return $order;
	}

    /**
     * identify all event line items on an order. Returns an array of line items.
     * @param Order $order
     *
     * @return array
     */
	private function getEventLineItems( Order $order ): array {
		$eventApiLineItems = [];
		foreach ( $order->lineItems as $lineItem ) {
			//get all the events this order
			if ( get_class( $lineItem->purchasable ) == \modules\Events\Elements\Schedule::class ) {
			    $event = $lineItem->purchasable->getEvent();
				if ( $event->apiId ) {
					$eventApiLineItems[] = $lineItem;
				}
			}
		}

		return $eventApiLineItems;
	}

    /**
     * Complete a booking with api.
     * @param Order $order
     * @param LineItem $lineItem
     *
     * @return bool|LineItem
     * @throws ConfirmError
     * @throws \Throwable
     * @throws \yii\db\Exception
     */
	public function complete( Order $order, LineItem $lineItem ) {
		$event = $lineItem->purchasable->getEvent();
        $currentOptions = $lineItem->getOptions();
		if($event->apiId){
		    if(isset($lineItem->options['apiBookingId'])&& $lineItem->options['apiBookingId']){
                if( !isset($currentOptions['apiCompleteBookingId']) || !$currentOptions['apiCompleteBookingId']) { //is this booking already existing - I don't want to duplicate!
                    $response = RezdyModule::getInstance()->api->completeBooking(
                        $event->apiId,
                        $lineItem->options['apiBookingId'], //booking id
                        $order->paymentCurrency, //currency
                        $lineItem->subtotal, //subtotal
                        $order->id, //order id
                        $lineItem->purchasable->startDateTime, //start datetime
                        $lineItem->qty
                    );
                    if ($response->isSuccessful()) {
                        // we should currently have a booking on hold. We can now attach the api booking id to
                        // the line item so we can finish the order it's been paid for.
                        $li = Commerce::getInstance()->lineItems->getLineItemById($lineItem->id);
                        $currentOptions                         = $li->getOptions();
                        $currentOptions['apiCompleteBookingId'] = $response->getData()->orderNumber;
                        $li->setOptions($currentOptions);
                        if (Commerce::getInstance()->getLineItems()->saveLineItem($li, false)) {
                            //update the purchasable stock levels:
                            $this->stockControl($li);

                            return $li;
                        } else {
                            $this->log("error saving apiCompleteBookingId", json_encode($lineItem->getErrors()), $order->id);
                            throw new ConfirmError("Error saving apiCompleteBookingId back to line item - bookingId: " . $response->getData()->orderNumber . " line item:" . $lineItem->id);
                        }
                    } else {
                        throw new ConfirmError("Error Confirming Booking - line item:" . $lineItem->id . ' - Response code ' . $response->getResponseCode() . ' received: ' . $response->response->getBody());
                    }
                }
                else{
                    // this lineitem already has a bookingConfirmation attached to it - something's probably gone wrong here.
                    // I don't think we should throw an error to the customer though.
                    // TODO: maybe we can throw internal error here for tracking. Maybe run delete function to remove the existing booking and re-book?
                    return $lineItem;
                }
            }
		    else{
                throw new ConfirmError("Error with Booking - line item:".$lineItem->id);
            }
		}
		else{
			$this->stockControl($lineItem);
            return $lineItem;
		}
	}

	/**
     * Updates stock
	 * @param LineItem $lineItem
	 * @param $response
	 *
	 * @throws \yii\db\Exception
	 */
	public function stockControl(LineItem $lineItem){
		// BookingResponse was going to be used to re-sync ticket stock
		// but weirdly, that data doesn't exist from the api - so rather
		// than making another  request to the api, I'll just do the
		// maths here and rely on the import to get the availability after
		// half an hour.

		// This seems a bit off to do it this way, but we need the data to
		// be super-up-to-date with no room for miss-matched stock levels.
		// really I'm just following what craft commerce does with their
		// stock control.
		Craft::$app->getDb()->createCommand()->update('{{%yalumba_eventschedule}}',
			['ticketsAvailable' => new Expression('ticketsAvailable - :qty', [':qty' => $lineItem->qty])],
			['id' => $lineItem->purchasable->id])->execute();
		// Update the stock
		$lineItem->purchasable->ticketsAvailable = (new Query())
			->select(['ticketsAvailable'])
			->from('{{%yalumba_eventschedule}}')
			->where('id = :scheduleId', [':scheduleId' => $lineItem->purchasable->id])
			->scalar();
		\Craft::$app->getTemplateCaches()->deleteCachesByElementId($lineItem->purchasable->id);

	}

    /**
     * Hold a booking with api. To ensure availability, this step should be taken before payment is taken.
     * If payment fails, you should cancel the booking.
     * @param Order $order
     *
     * @return Order
     * @throws HoldError
     * @throws SoldOutError
     * @throws \Throwable
     */
	public function hold( Order $order): Order {
		$eventApiLineItems = $this->getEventLineItems( $order );
		if ( count( $eventApiLineItems ) ) {
			//we need to see if we are ok to purchase this item.. doing so requires us to run
			//a check with the rezdy api and see what we get back.
			foreach ( $eventApiLineItems as $eventApiLineItem ) {
			    $event = $eventApiLineItem->purchasable->getEvent();
			    //we need availability before we hold this item.
                $currentOptions = $eventApiLineItem->getOptions();
                if( !isset($currentOptions['apiBookingId']) || !$currentOptions['apiBookingId']){ //is this booking already existing - I don't want to duplicate!
                    $availabilityResponse = RezdyModule::getInstance()->api->getRezdyAvailability([
                          'events'=>$event->apiId,
                          'startTime'=>$eventApiLineItem->purchasable->startDateTime,
                          'endTime'=>$eventApiLineItem->purchasable->startDateTime,
                          'qty'=>$eventApiLineItem->qty
                      ]);

                    if($availabilityResponse->isSuccessful()){
                        $response = RezdyModule::getInstance()->api->holdBooking(
                            $event->apiId,
                            $eventApiLineItem->purchasable->startDateTime,
                            $eventApiLineItem->qty,
                            $eventApiLineItem->subtotal,
                            $order );
                        if ( $response->isSuccessful() ) {
                            // we now have a booking on hold. We can now attach the api booking id to
                            // the line item so we can finish the order after payment..
                            $currentOptions                 = $eventApiLineItem->getOptions();
                            $currentOptions['apiBookingId'] = $response->getData()->orderNumber;
                            $eventApiLineItem->setOptions( $currentOptions );
                            //save the line item
                            Commerce::getInstance()->getLineItems()->saveLineItem( $eventApiLineItem, false );
                        } else {
                            //TODO: could be the email address is not valid - this needs looking at.
                            $order->addErrors([
                                'eventBooking'=>'Booking could not be held - please retry.'
                                ]);
                        }
                    }
                    else{
                        $order->addErrors([
                            'eventBooking'=> 'This event has just sold out. <a href="'.$event->url.'">Click here to change dates</a>'
                            ]);
                    }
                }
                else{
                    // this lineitem already has a bookingConfirmation attached to it - something's probably gone wrong here.
                    // I don't think we should throw an error to the customer though.
                    // TODO: maybe we can throw internal error here for tracking. Maybe run delete function to remove the existing booking and re-book?
                }
				//item is now booked but on hold via rezdy.
			}
		}

		return $order;
	}
}