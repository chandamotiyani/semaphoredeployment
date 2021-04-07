<?php


namespace modules\Rezdy\Services;


use craft\base\Component;
use craft\commerce\elements\Order;
use craft\elements\User;
use craft\helpers\DateTimeHelper;
use GuzzleHttp\Client;
use modules\Events\Elements\Schedule;
use modules\Rezdy\Responses\HoldBookingResponse;
use modules\Rezdy\Responses\ConfirmBookingResponse;
use modules\Rezdy\Responses\CancelBookingResponse;
use modules\Rezdy\Responses\ProductResponse;
use modules\Rezdy\Responses\ProductsResponse;
use modules\Rezdy\Responses\ScheduleResponse;
use \DateTime;
use modules\Rezdy\Responses\Traits\HasLog;

class RezdyApiService extends Component {
    use HasLog;

	private $protocol = 'https://';
	private $http;
	private $config;

	public function __construct( Client $http ) {
		$this->config = \Craft::$app->getConfig()->getConfigFromFile('services')['rezdy-api'];
		$this->http = $http;
	}

	public function sendMember(User $user){

		//TODO: evaluate response object and handle the failures.
	}

	public function getRezdyProducts(){
		$url      = $this->makeUrl( "products" );
		$options  = $this->makeOptions( [] );
		$response = new ProductsResponse($this->http,  $url, $options );
		return $response;
	}

	public function getRezdyProduct($product_id){
		$url      = $this->makeUrl( "products/$product_id" );
		$options  = $this->makeOptions( [] );
		$response = new ProductResponse($this->http, $url, $options );
		return $response;
	}

	public function cancelBooking($orderLineItem, $order){
		if((isset($orderLineItem->options['apiBookingId']) && $orderLineItem->options['apiBookingId']) ||
           (isset($orderLineItem->options['apiBookingConfirmId']) && $orderLineItem->options['apiBookingConfirmId'])){
			$bookingId = $orderLineItem->options['apiBookingId'];
			$url      = $this->makeUrl( "bookings/$bookingId" );
			$response = new CancelBookingResponse($this->http, $url, [] );
			return $response;
		}
		return false;
	}

    /**
     * @param string $productCode
     * @param string $apiBookingId
     * @param string $orderCurrency
     * @param float $orderSubtotal
     * @param int $orderId
     * @param DateTime $dateTime
     * @param int $qty
     *
     * @return ConfirmBookingResponse
     * @throws \Exception
     */
	public function completeBooking(string $productCode, string $apiBookingId, string $orderCurrency, float $orderSubtotal, int $orderId, \DateTime $dateTime, int $qty){

		$url      = $this->makeUrl( "bookings/$apiBookingId" );
		$body = [
            'sendNotifications'=>true,          // should we receive notification from rezdy for this order.
			'status'=>'CONFIRMED',              // we're confirming the existing order that should have been made before.
            "totalDue"=>0,                      // the remaining price due (after this payment goes through)
            "totalAmount"=>$orderSubtotal,
		];

		$body['items'] = $this->getItems($productCode, $dateTime, $qty, $orderSubtotal);
		$body['payments'] = $this->getPayment($orderSubtotal, $orderCurrency,new \DateTime(), $orderId);
		$postOptions  = $this->makeOptions($body);
        $this->log("complete rezdy booking", json_encode($body), $orderId);
		$response = new ConfirmBookingResponse($this->http,$url, $postOptions );
        $this->log("complete rezdy booking response", json_encode($response->getData()), $orderId);
		return $response;
	}

    /**
     * @param string $bookingId
     * @param DateTime $dateTime
     * @param int $qty
     * @param $lineItemSubtotal
     * @param $orderCurrency
     * @param Order $order
     *
     * @return HoldBookingResponse
     * @throws \Exception
     */
	public function holdBooking(string $bookingId,DateTime $dateTime, int $qty, $lineItemSubtotal, Order $order){
		$url      = $this->makeUrl( "bookings" );
		$items = $this->getItems($bookingId, $dateTime, $qty);
        $body = [
            "customer"=>[
                "addressLine" => $order->shippingAddress->address1,
                "addressLine2" => $order->shippingAddress->address2,
                "city" => $order->shippingAddress->city,
                "countryCode" => isset($order->shippingAddress->country->iso)?$order->shippingAddress->country->iso:"",
                "dob"=> $order->dateOfBirth,
                "email" => $order->email,
                "firstName" => $order->shippingAddress->firstName,
                "lastName" => $order->shippingAddress->lastName,
                "phone" => $order->phoneNumber,
                "postCode" => $order->shippingAddress->zipCode,
                "state" => (string) $order->shippingAddress->state->abbreviation,
            ],
            'payments'=>$this->getPayment($lineItemSubtotal, $order->paymentCurrency, new \DateTime(), $order->id),
			'status'=>'PROCESSING',
			'sendNotifications'=>false,
			'resellerReference'=>$order->id,
			'items'=>$items
		];
		$postOptions  = $this->makeOptions($body);
        $this->log("hold rezdy booking", json_encode($body), $order->id);
		$response = new HoldBookingResponse($this->http,$url, $postOptions );
        $this->log("hold rezdy booking response", json_encode($response->api_response_body), $order->id);
        return $response;
	}

	public function dateFormat(\DateTime $dateTime){
		//Docs say ISO8601, but it doesn't like the TZ being attached like PHP does it we'll need to do it ourselves:
//		echo('<pre>');
//		var_dump($options['startTime']->format(\DateTime::ISO8601)); //lolphp
//		var_dump($options['startTime']->format(\DateTime::ATOM)); //nope
//		exit();
		return $dateTime->format('Y-m-d\TH:i:sP');
	}

    public function localDateFormat(\DateTime$dateTime){
        return $dateTime->format('Y-m-d H:i:s');
    }

	public function getRezdyAvailability($options){
		// it uses non-standard querystring "productCode=123&productCode=321" etc. so
		// we need to do this ourselves too
        if(is_array($options['events'])){
            $query = "?productCode=".implode('&productCode=',$options['events']);
        }
        else{
            $query = "?productCode=".$options['events'];
        }

		$parsedStartTime = urlencode($this->localDateFormat($options['startTime']));
		$parsedEndTime = urlencode($this->localDateFormat($options['endTime']));
		$query .= "&startTimeLocal=$parsedStartTime";
		$query .= "&endTimeLocal=$parsedEndTime";

        if(isset($options['limit'])){
            $query .= "&limit=".$options['limit'];
        }
        else{
            $query .= "&limit=1000";
        }
		if(isset($options['qty'])){
		    $query .= "&minAvailability=".$options['qty'];
        }

		$url      = $this->makeUrl( "availability$query" );
		$options  = $this->makeOptions( [] );
		$response = new ScheduleResponse($this->http,  $url, $options );
		return $response;
	}

	private function getItems(string $bookingId, DateTime $dateTime, int $qty, float $orderSubtotal = null){
		return [
			[
				'productCode'=>$bookingId,
				"startTimeLocal"=> $dateTime->format('Y-m-d H:i:s'),
				"quantities"=>[
					//TODO: if this ever needs to accommodate different ticket prices etc, it
					// would be a good idea to pass this info in as a parameter (similar to
					// the schedule). Each ticket "type" would need ot be a separate call
					// anyways.
					[
                        "optionLabel"=> "Adult",
						"value"=>$qty
					]
				],
                "subtotal"=>$orderSubtotal,
                "amount"=>$orderSubtotal,
                "participants"=>$this->getParticipants($qty)
			]
		];
	}

	private function getPayment($orderSubtotal, $orderCurrency, DateTime $dateTime, $orderId){
        return [
            [
                'type'=>'CREDITCARD',
                'amount'=>$orderSubtotal,       //amount per line item ie $10 items * 5 qty = 50
                'currency'=>$orderCurrency,     //AUD.
                'date'=> $this->dateFormat($dateTime),
                'label'=> "Payment processed by yalumba.com. Order ID:".$orderId
            ]
        ];
    }

    private function getParticipants(int $qty){
        $participants = [];
	    for ($i = 0; $i < $qty; $i++) {
	        $participant = new \stdClass();
	        $participant->fields = [
	            [
	                "label"=>"First Name",
                    "value"=>""
                ],
                [
                    "label"=>"Last Name",
                    "value"=>""
                ]
            ];
            $participants[] = $participant;
        }
	    return $participants;
    }

	private function makeBody(User $user){
		$body=[

		];
		return $body;
	}

	private function makeOptions($body = NULL){
		$options = [
			"headers"=>[
				"apiKey"=>$this->config['api-key'],
				"Content-Type"=>"application/json",
			],
		];
		if($body){
			$options["body"] = json_encode($body, true);
		}
		return $options;
	}

	public function getCustomerGroupIdFromUser(User $user){
		//TODO: Are we happy with just returning the 1st one?
		foreach($user->groups as $group){
			return $this->config['customer-groups'][$group->handle];
		}
		return false;
		//TODO - what if the group doesn't exist in config?
	}

	private function makeUrl(string $url){
		return $this->protocol.$this->config['url']."/$url";
	}
}