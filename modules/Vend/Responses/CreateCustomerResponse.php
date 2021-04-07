<?php


namespace modules\Vend\Responses;


use modules\Vend\Responses\Contracts\Response as ResponseContract;
use \GuzzleHttp\Client;
use stdClass;

class CreateCustomerResponse implements ResponseContract {

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
			return true;
		}
		return false;
	}

	public function getData(){
		return $this->api_response_body->data;
	}


	//the response from the api is that this customer id already exists.
	public function customerExists(){
	    if($this->getResponseCode() == 400){
            if(isset($this->api_response_body->errors)){
                if(isset($this->api_response_body->errors->field)){
                    if(isset($this->api_response_body->errors->field->customer_code)){
                        if($this->api_response_body->errors->field->customer_code == "That customer code already exists."){
                            return true;
                        }
                    }
                }
            }
        }
	    return false;
    }

    public function getResponseCode(){
        return $this->response->getStatusCode();
    }
}