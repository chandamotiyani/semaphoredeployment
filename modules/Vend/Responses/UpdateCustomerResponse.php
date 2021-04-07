<?php


namespace modules\Vend\Responses;


use modules\Vend\Responses\Contracts\Response as ResponseContract;
use \GuzzleHttp\Client;
use stdClass;

class UpdateCustomerResponse implements ResponseContract {

	public $response;
	public $api_response_body;

    public function __construct(Client $http, string $url, array $options) {
        try{
            $this->response = $http->put( $url, $options );
        }
        catch(\Exception $e){
            $this->response = $e->getResponse();
        }
        $this->api_response_body = json_decode((string)$this->response->getBody());
    }


	public function isSuccessful(): bool{
		if($this->getResponseCode() == 200 || $this->getResponseCode() == 201){
		    if($this->api_response_body->data){
                return true;
            }
		}
		return false;
	}

	public function getData(){
		return $this->api_response_body->data;
	}

    public function getResponseCode(){
        return $this->response->getStatusCode();
    }
}