<?php


namespace modules\Vend\Responses\Contracts;

use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Client;
use \stdClass;

interface Response {
	public function __construct( Client $http, string $url, array $options);
	public function isSuccessful(): bool;
	public function getData();
	public function getResponseCode();
}