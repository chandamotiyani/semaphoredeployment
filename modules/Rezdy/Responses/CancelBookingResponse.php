<?php


namespace modules\Rezdy\Responses;


use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;
use modules\Rezdy\Responses\Contracts\Response as ResponseContract;

class CancelBookingResponse implements ResponseContract {

	private $response;
	private $api_response_body;

    public function __construct(Client $http, string $url, array $options) {
        try{
            $this->response = $http->delete( $url, $options );
        }
        catch(\Exception $e){
            $this->response = $e->getResponse();
        }
        $this->api_response_body = json_decode((string)$this->response->getBody());
    }


	public function isSuccessful(): bool{
		if($this->response->getStatusCode() == 200 || $this->response->getStatusCode() == 201){
			return true;
		}
		return false;
	}

	public function getData(){
		return false;
	}

    public function getResponseCode(){
        return $this->response->getStatusCode();
    }

    public function getResponse()
    {
        return $this->response;
    }
}