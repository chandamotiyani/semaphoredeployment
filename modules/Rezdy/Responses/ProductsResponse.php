<?php


namespace modules\Rezdy\Responses;


use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;
use modules\Rezdy\Responses\Contracts\Response as ResponseContract;

class ProductsResponse implements ResponseContract {

	private $response;
	private $api_response_body;

    public function __construct(Client $http, string $url, array $options) {
        try{
            $this->response = $http->get( $url, $options );
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
		return $this->api_response_body->products;
	}

    public function getResponseCode(){
        return $this->response->getStatusCode();
    }

    public function getResponse()
    {
        return $this->response;
    }
}