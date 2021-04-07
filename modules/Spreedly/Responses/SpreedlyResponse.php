<?php


namespace modules\Spreedly\Responses;


use craft\commerce\base\RequestResponseInterface;
use GuzzleHttp\Psr7\Response;
use modules\Spreedly\Errors\NotSupported;

class SpreedlyResponse implements RequestResponseInterface{

	private $api_response;
	private $api_response_body;

	/**
	 * SpreedlyResponse constructor.
	 *
	 * @param $api_response
	 */
	public function __construct( Response $api_response ) {
		$this->api_response_body = json_decode((string)$api_response->getBody());
		$this->api_response = $api_response;
	}

	/**
	 * Returns whether or not the payment was successful.
	 *
	 * @return bool
	 */
	public function isSuccessful(): bool {
		if($this->getCode() === "200" || $this->getCode() === "201"){
			if($this->api_response_body->transaction->succeeded == true){
				if($this->api_response_body->transaction->state == "succeeded"){
					if($this->api_response_body->transaction->response->success == true){
						if($this->api_response_body->transaction->response->cancelled == false){
							return true;
						}
					}
				}
			}
		}
		return false;
	}

	/**
	 * Returns whether or not the payment is being processed by gateway.
	 *
	 * @return bool
	 */
	public function isProcessing(): bool {
		return false;
	}

	/**
	 * Returns whether or not the user needs to be redirected.
	 *
	 * @return bool
	 */
	public function isRedirect(): bool {
		return false;
	}

	/**
	 * Returns the redirect method to use, if any.
	 *
	 * @return string
	 */
	public function getRedirectMethod(): string {
		return new NotSupported();
	}

	/**
	 * Returns the redirect data provided.
	 *
	 * @return array
	 */
	public function getRedirectData(): array {
		return new NotSupported();
	}

	/**
	 * Returns the redirect URL to use, if any.
	 *
	 * @return string
	 */
	public function getRedirectUrl(): string {
		return new NotSupported();
	}

	/**
	 * Returns the transaction reference.
	 *
	 * @return string
	 */
	public function getTransactionReference(): string {
		return $this->api_response_body->transaction->token;
	}

	/**
	 * Returns the response code.
	 *
	 * @return string
	 */
	public function getCode(): string {
		return $this->api_response->getStatusCode();
	}

	/**
	 * Returns the data.
	 *
	 * @return mixed
	 */
	public function getData() {
		return $this->api_response_body;
	}

	/**
	 * Returns the gateway message.
	 *
	 * @return string
	 */
	public function getMessage(): string {
		return $this->api_response_body->transaction->message;
	}

	/**
	 * Returns the gateway message.
	 *
	 * @return string
	 */
	public function getReference(): string {
		if(isset($this->api_response_body->transaction->token)){
			return $this->api_response_body->transaction->token;
		}
		return 'ERROR';
	}

	/**
	 * Perform the redirect.
	 *
	 * @return mixed
	 */
	public function redirect() {
		return new NotSupported();
	}
}