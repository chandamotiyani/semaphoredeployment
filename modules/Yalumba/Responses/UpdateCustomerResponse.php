<?php


namespace modules\Yalumba\Responses;


use GuzzleHttp\Client;
use modules\Yalumba\Responses\Traits\HasLog;
use \Psr\Http\Message\ResponseInterface;
use modules\Yalumba\Responses\Contracts\Response as ResponseContract;

class UpdateCustomerResponse {
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
		if($this->response->getStatusCode() == 200 || $this->response->getStatusCode() == 201){
		    $data = $this->getData();
		    if(isset($data->memberId)){
				return true;
			}
		}
		return false;
	}

	public function getData(){
		return $this->api_response_body;
	}

    public function getResponse()
    {
        return $this->response;
    }

    public function getResponseCode(){
        return $this->response->getStatusCode();
    }
}