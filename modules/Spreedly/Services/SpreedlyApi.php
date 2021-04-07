<?php


namespace modules\Spreedly\Services;


use craft\commerce\base\Gateway;
use craft\commerce\models\payments\BasePaymentForm;
use craft\commerce\models\payments\CreditCardPaymentForm;
use craft\commerce\models\PaymentSource;
use craft\commerce\models\Transaction;
use modules\Spreedly\Models\Payment;
use yii\base\Component;
use GuzzleHttp\Client;
use Craft;

class SpreedlyApi extends Component {

	private $http; //some httpClient compatible object - use guzzle.

	private $host = "https://core.spreedly.com";
	private $version = "v1";
	private $format = "json";
	private $environment_key;
	private $access_secret;

	private $gateway_token; // spreedly allows multiple gateways to be attached - we should grab this from the craft gateway settings page.

	private $gateway;
	private $payment;
	/**
	 * SpreedlyApi constructor.
	 *
	 * @param $guzzle
	 */
	public function __construct( Client $http ) {
		$this->http = $http;
	}

	public function deleteCard($token, $gateway){
		$this->gateway = $gateway;
		$spreedly_transaction = $this->makeTransaction( [
			"remove_from_gateway" => true,
		], 'transaction' );

		$url        = $this->makeUrl("payment_methods/".$token."/redact");
		$options     = $this->makeOptions( $spreedly_transaction );
		try{
			$response = $this->http->put($url, $options);
		}
		catch(\Exception $exception){
			$response = $exception->getResponse();
		}

		return $response;
	}

	public function getSupportedGateways(){
		try {
			//$transaction = $this->makeTransaction( [ "gateway_type" => "test" ], 'gateway' );
			$url         = $this->makeUrl( "gateways" );
			$options     = $this->makeOptions();

			$response = $this->http->get( $url, $options );
		}catch (\Exception $e){
			echo('<pre>');
			var_dump($e->getMessage());
			exit();
		}
		return $response;
	}


	public function createTestGateway(){
		try {
			$spreedly_transaction = $this->makeTransaction( [ "gateway_type" => "test" ], 'gateway' );
			$url         = $this->makeUrl( "gateways" );
			$options     = $this->makeOptions( $spreedly_transaction );
			$response = $this->http->post( $url, $options );
		}catch (\Exception $e){
				echo($e->getMessage());
				exit();
		}
		return $response;
	}


	public function makeRefundPayment($transaction){
		$this->gateway = $transaction->gateway;
		$spreedly_transaction = $this->makeTransaction( [
			"amount" => $this->parseCurrency($transaction->paymentAmount),
			"currency_code" => $transaction->paymentCurrency
		], 'transaction' );

		$url        = $this->makeUrl("transactions/".$transaction->reference."/credit");
		$options     = $this->makeOptions( $spreedly_transaction );
		try{
			$response = $this->http->post($url, $options);
		}
		catch(\Exception $exception){
			$response = $exception->getResponse();
		}

		return $response;
	}

	/**
	 * Adds a card into spreedly - returns with a token that is used elsewhere.
	 * @param CreditCardPaymentForm $cc
	 * @param $user
	 * @param $gateway
	 *
	 * @return \Psr\Http\Message\ResponseInterface
	 */
	public function addCard(CreditCardPaymentForm $cc, $user, $gateway){
		$this->gateway = $gateway;
		$spreedly_transaction = $this->makeTransaction([
			"credit_card"=> [
				"first_name"=> $cc->firstName,
				"last_name"=> $cc->lastName,
				"number"=> $cc->number,
				"verification_value"=> $cc->cvv,
				"month"=> $cc->month,
				"year"=> $cc->year
			],
			//"email"=>$user->email,    //if the email address is sent, Spreedly send emails to the customer - we don't want this.
			"retained"=>true,
			"allow_blank_name"=> false,
			"allow_expired_date"=> false,
			"allow_blank_date"=> false,
			"metadata"=>[
				"user_id"=>$user->id
			],
		], 'payment_method');

		$url = $this->makeUrl("payment_methods");
		$options = $this->makeOptions($spreedly_transaction);
		try{
			$response = $this->http->post($url, $options);
		}
		catch(\Exception $exception){
			$response = $exception->getResponse();
		}

		return $response;
	}

	/**
	 * we have the cost in Dollarydoos - Spreedly expects it in cents - so this is happening..
	 * @param $amount
	 *
	 * @return int
	 */
	private function parseCurrency($amount){
		return (int)($amount*100);
	}

	public function makePayment(Payment $payment,  $paymentSource){
		$this->gateway = $payment->gateway;
		$this->payment = $payment;
;
		$spreedly_transaction_standard = [
			"amount"=> $this->parseCurrency($this->payment->paymentAmount),
			"currency_code"=> @$this->payment->paymentCurrency,
			"order_id"=> @$this->payment->order->id,
			"ip"=>$_SERVER['REMOTE_ADDR'], //there's no nice craft method for this.
			"description"=> null, //TODO.
	//		"email"=> @$this->payment->email,   //if the email address is sent, Spreedly send emails to the customer - we don't want this.
			"stored_credential_initiator"=> "cardholder",   //TODO: this would change for subscriptions.
//			"stored_credential_reason_type"=> "recurring"   //TODO: this would be added for subscriptions.
			"shipping_address"=>[
				"name"=>  @$this->payment->shippingAddress->firstName." ".@$this->payment->shippingAddress->lastName,
				"address1"=> @$this->payment->shippingAddress->address1,
				"address2"=> @$this->payment->shippingAddress->address2,
				"city"=> @$this->payment->shippingAddress->city,
				"state"=> @$this->payment->shippingAddress->stateName,
				"zip"=> @$this->payment->shippingAddress->zipCode,
				"country"=> @$this->payment->shippingAddress->country->name,
				"phone_number"=> @$this->payment->shippingAddress->phone
			],
            "billing_address"=> [
                 "name"=> @$this->payment->billingAddress->firstName." ".$this->payment->billingAddress->lastName,
                 "address1"=> @$this->payment->billingAddress->address1,
                 "address2"=> @$this->payment->billingAddress->address2,
                 "city"=> @$this->payment->billingAddress->city,
                 "state"=> @$this->payment->billingAddress->stateName,
                 "zip"=> @$this->payment->billingAddress->zipCode,
                 "country"=> @$this->payment->billingAddress->country->name,
                 "phone_number"=> @$this->payment->billingAddress->phone
            ]
		];
		if($paymentSource instanceof PaymentSource){
			//if this is set, we need to charge using the token id instead.
			$spreedly_transaction = [
				"payment_method_token"=>$paymentSource->token
			];
		}
		else if($paymentSource instanceof BasePaymentForm){
			$spreedly_transaction = [
				"credit_card"=> [
					"first_name"=> $paymentSource->firstName,
					"last_name"=> $paymentSource->lastName,
					"number"=> $paymentSource->number,
					"verification_value"=> $paymentSource->cvv,
					"month"=> $paymentSource->month,
					"year"=> $paymentSource->year
				],
				//"retain_on_success"=> true,                     //TODO: base this off settings.
				"allow_blank_name"=> false,
				"allow_expired_date"=> false,
				"allow_blank_date"=> false,
			];
		}
		else{
			echo('<pre>');
			var_dump('unrecognised payment source - error.');
			var_dump($paymentSource);
			exit();
		}

		$spreedly_transaction = $this->makeTransaction(array_merge($spreedly_transaction, $spreedly_transaction_standard));

		$url = $this->makeUrl("gateways/".Craft::parseEnv($this->gateway->gateway_token)."/purchase");
		$options = $this->makeOptions($spreedly_transaction);

		// I don't want Guzzle to be handling the error messages here - we have
		// a response object that should allow for handling whatever the api
		// returns.
		try{
			$response = $this->http->post($url, $options);
		}
		catch(\Exception $exception){
			$response = $exception->getResponse();
		}
		return $response;
	}

	/**
	 * creates the url as it should be used to make requests
	 * @param $url
	 *
	 * @return string
	 */
	private function makeUrl($url){
		return "$this->host/$this->version/$url.$this->format";
	}


	/**
	 * make a transaction based on data array.
	 * @param $data
	 * @param string $type
	 *
	 * @return \stdClass
	 */
	public function makeTransaction($data, $type='transaction'){
		$t = new \stdClass();
		$t->{$type} = (object)$data;
		return $t;
	}

	private function makeOptions($body = NULL){

		$options = [
			"auth"=>[
				Craft::parseEnv($this->gateway->environment_key),
				Craft::parseEnv($this->gateway->access_secret),
			],
			"headers"=>[
				"Content-Type"=>"application/$this->format",
			],
			//'debug' => true

		];
		if($body){
			$options["body"] = json_encode($body, true);
		}
		return $options;
	}

}
