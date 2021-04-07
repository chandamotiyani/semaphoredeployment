<?php


namespace modules\Yalumba\Responses\Contracts;


use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;

interface Response {
	public function __construct( Client $http, string $url, array $options);
	public function isSuccessful(): bool;
	public function getData();
	public function getResponse();
    public function getResponseCode();
}