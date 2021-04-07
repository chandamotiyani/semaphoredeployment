<?php


namespace modules\Yalumba\Services;


use craft\base\Component;
use craft\commerce\elements\Order;
use craft\commerce\elements\Product;
use craft\commerce\elements\Variant;
use craft\commerce\models\LineItem;
use craft\commerce\Plugin as Commerce;
use craft\elements\User;
use craft\fields\data\SingleOptionFieldData;
use craft\helpers\DateTimeHelper;
use GuzzleHttp\Client;
use modules\Events\Elements\Schedule;
use modules\Memberships\MembershipModule;
use modules\Orders\OrdersModule;
use modules\Yalumba\Errors\YalumbaException;
use modules\Yalumba\Responses\CreateCustomerResponse;
use modules\Yalumba\Responses\DisableCustomerResponse;
use modules\Yalumba\Responses\OrderResponse;
use \Craft;
use modules\Yalumba\Responses\Traits\HasLog;
use modules\Yalumba\Responses\UpdateCustomerResponse;

class YalumbaApi extends Component {

    use HasLog;
    private $http;
	private $config;

	public function __construct( Client $http ) {
		$this->config = \Craft::$app->getConfig()->getConfigFromFile( 'services' )['yalumba-api'];
		$this->http   = $http;
	}

	public function sendYalumbaOrder( Order $order ) {
		$customer = $order->customer;
		$url  = $this->makeUrl( "create-sales-order/yalumba-dot-com/v1/create" );

		//dateOrdered is -sometimes- not set. As such I'll set it to now according to php,
        //which should handle any timezone trickery magically.
		if($order->dateOrdered && is_int($order->dateOrdered) && get_class($order->dateOrdered, \DateTime::class)){
            $orderDate = $order->dateOrdered->format( 'Y-m-d' );
		}
		else{
            $orderDate = new \DateTime();
		}

		$body = [
			"order" => [
				"source"   => "YALAUS",
				"header"   => [
					"orderDate"   => (string) $orderDate->format('Y-m-d'),
					//jdeCustNum optionally inserted below
					"customerExternalId" => (string) $customer->id,
					"email"       => (string) $order->email,         //this is optional, but I'll send it regardless
					"phoneNumber" => (string) $order->phoneNumber,         //this is optional, but I'll send it regardless
					"detail"      => $this->getLineItemOptions( $order ),
				],
				"delivery" => [
					"deliverySuburb"        => (string) $order->shippingAddress->city,
					"deliveryState"         => (string) $order->shippingAddress->state->abbreviation,
					"deliveryPostcode"      => (string) $order->shippingAddress->zipCode,
					"deliveryName"          => ((string) $order->shippingAddress->firstName)." ".((string) $order->shippingAddress->lastName),
					"deliveryInstructions1" => (string) $order->shippingAddress->attention,
					"deliveryCountry"       => (string) $order->shippingAddress->country->iso,//"AU",
					"deliveryAddress1"      => (string) $order->shippingAddress->address1,//"Address 1"
					"deliveryAddress2"      => (string) $order->shippingAddress->address2,//"Address 2",
				],
				"billing"  => [
					"billingSuburb"   => (string) $order->billingAddress->city,//"Angaston",
					"billingState"    => (string) $order->billingAddress->state->abbreviation,//"SA",
					"billingPostcode" => (string) $order->billingAddress->zipCode,//"5353",
					"billingName"     => ((string) $order->billingAddress->firstName)." ".((string) $order->billingAddress->lastName),//"Bob Smith",
					"billingCountry"  => (string) $order->billingAddress->country->iso,//"AUS",
					"billingAddress1" => (string) $order->billingAddress->address1,//"Address 2"
					"billingAddress2" => (string) $order->billingAddress->address2,//"Address 1",
				],
			],
		];
		//if there's a member we need to pass it in to the request.
		if ( $customer->user && isset($customer->user->yalumbaCustomerId) && $customer->user->yalumbaCustomerId ) {
			$body["order"]["header"]["jdeCustNum"] = (int)$customer->user->yalumbaCustomerId;
		}
		
//        var_dump($body);
//		throw new YalumbaException('stop here');

		$options = $this->makeOptions( $body );
        $this->log("create yalumba order", json_encode($body), $order->id);
		$response = new OrderResponse( $this->http,$url, $options );
        $this->log("response: create yalumba order", json_encode($response->getData()), $order->id);
        if ( $response->isSuccessful() ) {
            return $response;
        }
        else{
            \Craft::getLogger()->log($body, \yii\log\Logger::LEVEL_TRACE, "yalumba_api");
            throw new YalumbaException('Yalumba API data send failed - Response code '.$response->getResponseCode() . ' received: '.$response->response->getBody());
        }
	}

	public function sendYalumbaCustomer($user, $groupIds){
		if($user->yalumbaCustomerId){
			//we need to update
			return $this->updateYalumbaCustomer($user, $groupIds);
		}
		else{
			//we need to create
			return $this->createYalumbaCustomer($user, $groupIds);
		}
	}

	public function createYalumbaCustomer( $user, $groupIds ) {
	    // Chanda check again if customer Id exist
        if(!$user->yalumbaCustomerId){
            $url     = $this->makeUrl( "create-customer/wine-club/v1/create" );
            $membership = false;
            if($groupIds && is_array($groupIds)){
                if(count($groupIds)){
                    $membership = MembershipModule::getInstance()->members->getYalumbaMemberUserGroup($groupIds);
                }
            }
            if(!$membership){
                $membership = MembershipModule::getInstance()->members->getYalumbaMemberUserGroup(\Craft::$app->userGroups->getGroupsByUserId($user->id));
            }
            if($user->dateOfBirth){
                $dob = $user->dateOfBirth->format('Y-m-d');
            }
            else{
                $dob = '';
            }
            $body = [
                "source"   => "YALAUS",
                "customer" => [
                    "memberId"    => $user->uid,
                    "firstName"   => $user->firstName,
                    "lastName"    => $user->lastName,
                    "email"       => $user->email,
                    //"phoneNumber" => $user->phoneNumber, //this is optional, so this is set below.
                    "dateOfBirth" => $dob,
                    "membershipLevel"=> $membership->handle,
                    "membershipSource"=> "https://yalumba.com" //TODO: find a way to make this dynamic?
                ]
            ];
            if(isset( $user->phoneNumber) &&  $user->phoneNumber){
                $body["customer"]["phoneNumber"] = $user->phoneNumber;
            }

            $options = $this->makeOptions( $body );

            $response = new CreateCustomerResponse( $this->http, $url, $options );
            $this->log("create yalumba customer", json_encode($body), $user->id);

            if ( $response->isSuccessful() ) {
                if($response->getCustomerIdentifier()){
                    $user->yalumbaCustomerId = $response->getCustomerIdentifier();
                    // Chanda - Show Yalumba API response log for Yalumba create customer request
                    @$this->log("response: create yalumba customer", json_encode($response->getData()), $user->id);
                }
                else{
                    throw new YalumbaException('Yalumba API failed - Could not get customer identifier. Response code '.$response->getResponseCode() . ' received: '.$response->response->getBody());
                }
                // save the user with the yalumba id back to the system in a custom field.
                // WE CAN'T USE ELEMENT->SAVE HERE BECAUSE IT WILL TRIGGER ANOTHER EVENT.
                // SO WE HAVE TO RUN THIS QUERY MANUALLY UNLESS THERE's SOME WAY OF DISABLING
                // THE EVENT NICELY?
                $db = Craft::$app->getDb();
                $params = [
                    'userId'=>$user->id,
                    'yalumbaCustomerId'=>$user->yalumbaCustomerId
                ];
                $sql = "UPDATE {{%content}} SET field_yalumbaCustomerId = :yalumbaCustomerId WHERE elementId = :userId";
                $db->createCommand($sql, $params)->execute();
                return $user;
            }
            else{
                \Craft::getLogger()->log($body, \yii\log\Logger::LEVEL_TRACE, "yalumba_api");
                throw new YalumbaException('Yalumba API data send failed - Response code '.$response->getResponseCode() . ' received: '.$response->response->getBody());
            }
        }

		return false;
	}


	public function updateYalumbaCustomer( $user, $groupIds ) {
		$url     = $this->makeUrl( "update-customer/wine-club/v1/update" );
        $oldMembership = false;
        if($groupIds && is_array($groupIds)){
            if(count($groupIds)){
                $oldMembership = MembershipModule::getInstance()->members->getYalumbaMemberUserGroup($groupIds);
            }
        }
        if(!$oldMembership){
            $oldMembership = MembershipModule::getInstance()->members->getYalumbaMemberUserGroup(\Craft::$app->userGroups->getGroupsByUserId($user->id));
        }
		$userGroups = Craft::$app->userGroups->getGroupsByUserId($user->id);
		$newMembership = MembershipModule::getInstance()->members->getYalumbaMemberUserGroup($userGroups);
		$body = [
			"source"   => "YALAUS",
			"customer" => [
				"memberId"    => $user->yalumbaHubId?$user->yalumbaHubId:$user->uid,
				"firstName"   => $user->firstName,
				"lastName"    => $user->lastName,
				"email"       => $user->email,
				//"phoneNumber" => $user->phoneNumber, //this is optional so this is set below.
				"dateOfBirth" => $user->dateOfBirth->format('d/m/Y'), //careful with the formatting on this - note that it's different to the create function.
				"fromMembershipLevel"=> $oldMembership->handle,
				"toMembershipLevel"=> $newMembership->handle,
				"membershipSource"=> "https://yalumba.com" //TODO: find a way to make this dynamic?
			]
		];

        if(isset( $user->phoneNumber) &&  $user->phoneNumber){
            $body["customer"]["phoneNumber"] = $user->phoneNumber;
        }

		$options = $this->makeOptions( $body );

		$response = new UpdateCustomerResponse( $this->http, $url, $options );
        $this->log("update yalumba customer", json_encode($body), $user->id);

		if ( $response->isSuccessful() ) {
			return $user;
		}
        else{
            \Craft::getLogger()->log($body, \yii\log\Logger::LEVEL_TRACE, "yalumba_api");
            throw new YalumbaException('Yalumba API data send failed - Response code '.$response->getResponseCode() . ' received: '.$response->response->getBody());
        }

		return false;
	}

	public function disableYalumbaCustomer( $user ) {
		$url     = $this->makeUrl( "disable-customer/wine-club/v1/disable" );
		$userGroups = Craft::$app->userGroups->getGroupsByUserId($user->id);
		$membership = MembershipModule::getInstance()->members->getYalumbaMemberUserGroup($userGroups);
		if($membership){
            $body = [
                "source"   => "YALAUS",
                "customer" => [
                    "memberId"    => $user->yalumbaHubId?$user->yalumbaHubId:$user->uid,
                    "firstName"   => $user->firstName,
                    "lastName"    => $user->lastName,
                    "email"       => $user->email,
                    "membershipLevel"=> $membership->handle,
                ]
            ];
            $options = $this->makeOptions( $body );

            $response = new DisableCustomerResponse( $this->http,$url, $options );
            $this->log("disable yalumba customer", json_encode($body), $user->id);

            if ( $response->isSuccessful() ) {
                return true;
            }
            else{
                \Craft::getLogger()->log($body, \yii\log\Logger::LEVEL_TRACE, "yalumba_api");
                throw new YalumbaException('Yalumba API Could not delete user: '.$user->id.' - Response code '.$response->getResponseCode() . ' received: '.$response->response->getBody());
            }
        }
		//user is not a member according to craft.
		return false;
	}

	/**
	 * creates the url as it should be used to make requests
	 *
	 * @param $url
	 *
	 * @return string
	 */
	private function makeUrl( $url ) {
	    $host = $this->config['host'];
		return "$host/$url";
	}

	private function makeOptions( $body = null ) {

		$options = [
			"headers" => [
				"Content-Type"              => "application/json",
				"Ocp-Apim-Subscription-Key" => $this->config['subscription-key'],
			],
		];
		if ( $body ) {
			$options["body"] = json_encode( $body );
		}

		return $options;
	}

	public function getLineItemOptions( Order $order )
    {
        $options = [];
        //TODO: should I be using snapshot data?
        foreach ($order->lineItems as $lineItem) {

            $pricePostTax = $lineItem->getSubtotal();
            foreach($order->getAdjustmentsByType('tax') as $taxAdjuster){
                //manually apply tax rate - hackity hack.
                $pricePostTax = $this->calcTax($taxAdjuster, $lineItem->getSubtotal());
            }

            $purchasable = $lineItem->purchasable;
            if(isset($purchasable->product->phonetic) && $purchasable->product->phonetic){
                $options = $this->addOption($options, [
                    "unitOfMeasure" => (string)$purchasable->product->unitOfMeasure,         //"BT",
                    "qtyOrdered"    => (int)$lineItem->qty,                         //1,
                    "phonetic"      => (string)$purchasable->product->phonetic,              //"YALBSV166",
                    "extendedPrice" => (float)$pricePostTax,                 //22.45, //TODO: confirm id this is the price of an individual or the the qty
                    "itemsPerCase"  => (int)$purchasable->product->itemsPerCase,              //6
                    "warehouse"     => "ANG" // used for yalumba warehousing systems.
                ]);
            }
            else if(isset($purchasable->phonetic) && $purchasable->phonetic){
                $options = $this->addOption($options, [
                    "unitOfMeasure" => (string)$purchasable->unitOfMeasure,         //"BT",
                    "qtyOrdered"    => (int)$lineItem->qty,                         //1,
                    "phonetic"      => (string)$purchasable->phonetic,              //"YALBSV166",
                    "extendedPrice" => (float)$pricePostTax,                 //22.45, //TODO: confirm id this is the price of an individual or the the qty
                    "itemsPerCase"  => (int)$purchasable->itemsPerCase,              //6
                    "warehouse"     => "ANG" // used for yalumba warehousing systems.
                ]);
            }
            else{
                //no phonetic - is this a wine pack?
                if(isset($lineItem->purchasable->product) && $lineItem->purchasable->product->type->handle == 'gifts'){
                    $options = array_merge($options, $this->addPack($order, $lineItem));
                }

                //no phonetic - is this an event?
                if(get_class($lineItem->purchasable) == Schedule::class ){
                    $event = $lineItem->purchasable->getEvent();
                    if($event && $event->phonetic){
                        $options = $this->addOption($options, [
                            "unitOfMeasure" => (string)$event->unitOfMeasure,               //"BT",
                            "qtyOrdered"    => (int)$lineItem->qty,                         //1,
                            "phonetic"      => (string)$event->phonetic,                    //"YALBSV166",
                            "extendedPrice" => (float)$pricePostTax,                 //22.45, //TODO: confirm id this is the price of an individual or the the qty
                            "itemsPerCase"  => (int)$event->itemsPerCase,                    //6
                            /* Chanda - 12 Feb 2021 Yalumba want to change it for giftoptions from ANG to CDS */
                            /* Chanda - 15 Feb 2021 Hotfix */
                            "warehouse"     => "ANG" // used for yalumba warehousing systems.
                            /* End */
                        ]);
                    }
                    else{
                        if(!$event->phonetic){
                            throw new YalumbaException("Yalumba API create order failed - Event missing phonetic - $event->id");
                        }
                        //otherwise, not an event.
                    }
                }
            }
            // and add the gift options
            $lineItemOptions = $lineItem->getOptions();
            if($lineItemOptions){
                foreach ($lineItem->adjustments as $adjustment) {
                    if ($adjustment->type == 'gift_options') {
                        if($adjustment->amount > 0){ //if price is 0 we don't need to send this to yal.
                            $product = Commerce::getInstance()->products->getProductById($adjustment->sourceSnapshot["gift_product_id"]);
                            if($product && $product->phonetic){

                                $pricePostTax = $adjustment->amount;
                                foreach($order->getAdjustmentsByType('tax') as $taxAdjuster){
                                    //manually apply tax rate - hackity hack.
                                    $pricePostTax = $this->calcTax($taxAdjuster, $adjustment->amount);
                                }

                                $options = $this->addOption($options, [
                                    "unitOfMeasure" => (string)$product->unitOfMeasure,            //"BT",
                                    "qtyOrdered"    => (int)$lineItem->qty,                        //1,
                                    "phonetic"      => (string)$product->phonetic,                 //"YALBSV166",
                                    "extendedPrice" => (float)$pricePostTax,                 //22.45,
                                    "itemsPerCase"  => (int)$product->itemsPerCase,                 //6
                                    "warehouse"     => "ANG" // used for yalumba warehousing systems.
                                ]);
                            }
                        }
                    }
                }
            }
        }

        //we also, if this order has shipping, add the shipping line item
        $shippingProductArray = $this->getShippingProductArray($order);
        if ($shippingProductArray) {
            $options[] = $this->getShippingLineItem($order, $shippingProductArray);
        }

        return $options;
    }

    /**
     * adds a new line item
     * @param $options
     * @param $option
     *
     * @return mixed
     */
    public function addOption($options, $option){
        //TODO: we might need some logic in here to deal with duplicate phonetics?
        $options[] = $option;
        return $options;
    }

    public function addPack(Order $order, LineItem $lineItem){
	    $options = [];
        $wines = $lineItem->purchasable->product->productinfogift->wine->all();
        $count = 0;
        foreach($wines as $wine){
            $wineProducts = $wine->products->all();
            foreach($wineProducts as $wineProduct){
                $option = [
                    "unitOfMeasure" => (string)$wineProduct->unitOfMeasure,  //"BT",
                    "qtyOrdered"    => (int)$wine->qty,                      //1,
                    "phonetic"      => (string)$wineProduct->phonetic,       //"YALBSV166",
                    //"extendedPrice" => (float)$lineItem->subtotal,           //THIS GETS DIVIDED BY THE AMOUNT OF PRODUCTS BELOW ONCE WE'VE COUNTED THEM ALL
                    "itemsPerCase"  => (int)$wineProduct->itemsPerCase,       //6
                    "warehouse"     => "ANG" // used for yalumba warehousing systems.
                ] ;
                $count = $count + $wine->qty;
                $options[] =$option;
            }
        }

        $orderTaxAdjuster = NULL;
        foreach($order->getAdjustmentsByType('tax') as $taxAdjuster){
            //manually apply tax rate - hackity hack.
            $orderTaxAdjuster = $taxAdjuster;
        }

        //work out the price per item, then multiply by the qty of each wine
        if($count){
            $perItem = $lineItem->subtotal / $count;
            foreach($options as &$option){
                if(isset($orderTaxAdjuster)){
                    $option['extendedPrice'] = $this->calcTax($orderTaxAdjuster, round($perItem*$option["qtyOrdered"], 2));
                }
                else{
                    $option['extendedPrice'] = round($perItem*$option["qtyOrdered"], 2);
                }

            }
        }
	    return $options;
    }

    public function calcTax($taxAdjuster, $amount){
        $percentage = ($taxAdjuster->getSourceSnapshot()['rate']*100);
        $value = $amount;
        $taxAdded = (( $value * $percentage ) / (100 + $percentage));
        return  ($amount - $taxAdded);
    }

    /**
     * add the shipping line item
     * @param Order $order
     * @param array $shippingProductArray
     *
     * @return array
     */
	public function getShippingLineItem(Order $order, array $shippingProductArray){
        $amount = 0.00;
        foreach($order->getAdjustmentsByType('shipping') as $shippingAdjuster){
            $pricePostTax = $shippingAdjuster->amount;
            foreach($order->getAdjustmentsByType('tax') as $taxAdjuster){
                //manually apply tax rate - hackity hack.
                $percentage = ($taxAdjuster->getSourceSnapshot()['rate']*100);
                $value = $shippingAdjuster->amount;
                $taxAdded = (( $value * $percentage ) / (100 + $percentage));
                $pricePostTax = $shippingAdjuster->amount - $taxAdded;
            }
        }
        if(isset($pricePostTax)){
            return [
                "unitOfMeasure" => (string) $shippingProductArray['unitOfMeasure'],  //"BT",
                "qtyOrdered"    => (int) 1, //we assume we'll only get 1 shipping?
                "phonetic"      => (string) $shippingProductArray['phonetic'],  //"YALBSV166",
                "extendedPrice" => (float) $pricePostTax,
                "itemsPerCase"  => (int) $shippingProductArray['itemsPerCase'],
                "warehouse"     => "ANG" // used for yalumba warehousing systems.
            ];
        }
            
	}


    /**
     * Loop through all adjusters on the order - if any are "shipping" adjusters, return the shipping method's phonetic from the config.
     * @param Order $order
     *
     * @return bool|mixed
     */
	public function getShippingProductArray(Order $order){
        foreach($order->getAdjustmentsByType('shipping') as $shippingAdjuster){
            $shippingMethods = Craft::$app->getConfig()->getConfigFromFile('commerce')['shippingMethods'];
            return $shippingMethods[$shippingAdjuster->name];
        }
	    return false;
    }
}