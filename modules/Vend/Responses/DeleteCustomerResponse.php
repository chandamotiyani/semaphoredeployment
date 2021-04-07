<?php


namespace modules\Vend\Responses;


use GuzzleHttp\Client;
use modules\Vend\Responses\Contracts\Response as ResponseContract;
use Psr\Http\Message\ResponseInterface;

class DeleteCustomerResponse implements ResponseContract {

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
            return true;
        }
		return false;
	}

	public function getData(){
		return $this->api_response_body;
	}

    public function getResponseCode(){
        return $this->response->getStatusCode();
    }
}