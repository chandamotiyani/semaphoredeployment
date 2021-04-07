<?php


namespace modules\Rezdy\Responses;


use GuzzleHttp\Client;
use modules\Rezdy\Responses\Traits\HasLog;
use Psr\Http\Message\ResponseInterface;
use modules\Rezdy\Responses\Contracts\Response as ResponseContract;

class HoldBookingResponse implements ResponseContract {
    public $response;
	public $api_response_body;


    public function __construct(Client $http, string $url, array $options) {
        try{
            $this->response = $http->post( $url, $options );
        }
        catch(\Exception $e){
            $this->response = $e->getResponse();
        }
        $this->api_response_body = json_decode((string)$this->response->getBody());
    }


	public function isSuccessful(): bool{
		//TODO: do some more checks in here. Need to check the _validity of the data

		if($this->response->getStatusCode() == 200 || $this->response->getStatusCode() == 201){
			if(isset($this->api_response_body->requestStatus)){
				if($this->api_response_body->requestStatus->success){
					if($this->api_response_body->booking){
						return true;
					}
				}
			}
		}
		return false;
	}

	public function getData(){
		return $this->api_response_body->booking;
	}

    public function getResponseCode(){
        return $this->response->getStatusCode();
    }

    public function getResponse()
    {
        return $this->response;
    }
}