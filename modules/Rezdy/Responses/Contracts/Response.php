<?php


namespace modules\Rezdy\Responses\Contracts;

use GuzzleHttp\Client;

interface Response {

    public function __construct(Client $http, string $url, array $options);
	public function isSuccessful(): bool;
	public function getData();
	public function getResponseCode();
}