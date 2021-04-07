<?php

namespace Tests\unit;

use Codeception\Test\Unit;

use modules\Events\EventsModule;
use modules\Rezdy\Responses\HoldBookingResponse;
use modules\Rezdy\Responses\ProductResponse;
use modules\Rezdy\Responses\ProductsResponse;
use modules\Rezdy\Responses\ScheduleResponse;
use modules\Rezdy\RezdyModule;
use UnitTester;
use Craft;
use DateTime;

class RezdyTest extends Unit
{
	/**
	 * @var UnitTester
	 */
	protected $tester;

	protected $module;


	public function _before() {
		$this->module = RezdyModule::getInstance();
	}

	public function testEventsGet() {
		$response = $this->module->api->getRezdyProducts();
		$this->assertInstanceOf(ProductsResponse::class,$response);
		$this->assertTrue($response->isSuccessful());
		$this->assertIsArray($response->getData());
		$this->assertNotEmpty($response->getData());
	}

	public function testEventGet() {
		//TODO: get valid/invalid events
		$response = $this->module->api->getRezdyProducts();
		$response = $this->module->api->getRezdyProduct($response->getData()[0]->productCode);
		$this->assertInstanceOf(ProductResponse::class,$response);
		$this->assertTrue($response->isSuccessful());
		$this->assertNotEmpty($response->getData()->productCode);
	}

	public function testEventAvailability() {
		$response = $this->module->api->getRezdyProducts();
		$productCodeIds = [];
		foreach($response->getData() as $product){
			$productCodeIds[] = $product->productCode;
		}
		$options = [
			'events'=>$productCodeIds,
			'startTime'=>new \DateTime(),
			'endTime'=>(new \DateTime())->add(new \DateInterval('P1Y'))
		];

		$response = $this->module->api->getRezdyAvailability($options);
		$this->assertInstanceOf(ScheduleResponse::class,$response);
		$this->assertTrue($response->isSuccessful());
		$this->assertIsArray($response->getData());
		$this->assertNotEmpty($response->getData());
		foreach($response->getData() as $session){
			$this->assertNotFalse(\DateTime::createFromFormat('Y-m-d H:i:s',$session->startTimeLocal));
			$this->assertNotFalse(\DateTime::createFromFormat('Y-m-d H:i:s',$session->endTimeLocal));
		}
	}

	public function testEventBookHold() {
		$response = $this->module->api->getRezdyProducts();
		$productCodeIds = [];
		foreach($response->getData() as $product){
			$productCodeIds[] = $product->productCode;
		}
		$options = [
			'events'=>$productCodeIds,
			'startTime'=>new \DateTime(),
			'endTime'=>(new \DateTime())->add(new \DateInterval('P1Y'))
		];

		$availabilityResponse = $this->module->api->getRezdyAvailability($options);

		$productCode = $availabilityResponse->getData()[0]->productCode;
		$dateTime =  $availabilityResponse->getData()[0]->startTimeLocal;
		$qty = 1;
		$reference = 'testOrder-'.uniqid();
		$this->assertNotEmpty($productCode);
		$this->assertNotFalse($dateTime);
		$response = $this->module->api->holdBooking($productCode, $dateTime, $qty, $reference);
		$this->assertInstanceOf(HoldBookingResponse::class, $response);
		$this->assertTrue($response->isSuccessful());
		$this->assertEquals('PROCESSING', $response->getData()->status);
	}

	public function testEventBookConfirm() {
		$response = $this->module->api->getRezdyProducts();
		$productCodeIds = [];
		foreach($response->getData() as $product){
			$productCodeIds[] = $product->productCode;
		}
		$options = [
			'events'=>$productCodeIds,
			'startTime'=>new \DateTime(),
			'endTime'=>(new \DateTime())->add(new \DateInterval('P1Y'))
		];

		$availabilityResponse = $this->module->api->getRezdyAvailability($options);

		$productCode = $availabilityResponse->getData()[0]->productCode;
		$dateTime =  DateTime::createFromFormat('Y-m-d H:i:s', $availabilityResponse->getData()[0]->startTimeLocal);
		$qty = 1;
		$reference = 'testOrder-'.uniqid();
		$this->assertNotEmpty($productCode);
		$this->assertNotFalse($dateTime);
		$response = $this->module->api->holdBooking($productCode, $dateTime, $qty, $reference);
		$this->assertInstanceOf(HoldBookingResponse::class, $response);
		$this->assertTrue($response->isSuccessful());
		$this->assertEquals('PROCESSING', $response->getData()->status);
		$apiBookingId = $response->getData()->orderNumber;
		$this->assertNotEmpty($apiBookingId);

		//now we can confirm this booking:
		$response = $this->module->api->completeBooking($productCode, $apiBookingId, 'AUD', 50.50, 123, $dateTime, 1);
		$this->assertInstanceOf(HoldBookingResponse::class, $response);
		$this->assertTrue($response->isSuccessful());
		$this->assertEquals('CONFIRMED', $response->getData()->status);
	}
}
