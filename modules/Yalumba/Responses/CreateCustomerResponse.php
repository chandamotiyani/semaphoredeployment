<?php


namespace modules\Yalumba\Responses;


use GuzzleHttp\Client;
use modules\Yalumba\Responses\Traits\HasLog;
use \Psr\Http\Message\ResponseInterface;
use modules\Yalumba\Responses\Contracts\Response as ResponseContract;

class CreateCustomerResponse {
    use HasLog;
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
		if($this->getResponseCode() == 200 || $this->getResponseCode() == 201){
			if($this->getCustomerIdentifier()){
				return true;
			}
		}
		return false;
	}

    /**
     * Yalumba api appears to sometimes get customerNumber, sometimes customerId.
     * I'll need to implement oth for this to work reliably.
     */
	public function getCustomerIdentifier(){
        if(isset($this->getData()->customerId)){
            return $this->getData()->customerId;
        }
        if(isset($this->getData()->customerNumber)){
            return $this->getData()->customerNumber;
        }
        return false;
    }

	public function getData(){
		return $this->api_response_body;
	}

	public function getResponseCode(){
        return $this->response->getStatusCode();
    }

    public function getResponse()
    {
        return $this->response;
    }
}