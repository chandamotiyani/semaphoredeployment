<?php

namespace Tests\unit;

use Codeception\Test\Unit;

use modules\Events\EventsModule;
use modules\Orders\OrdersModule;
use UnitTester;
use Craft;

class OrdersTest extends Unit
{
	/**
	 * @var UnitTester
	 */
	protected $tester;

	protected $module;


	public function _before(){
		$this->module = OrdersModule::getInstance();
	}

	public function testServices()
	{
		$this->assertTrue(true);
		//go through all the event services for now.
//		$schedule = $this->module->schedule->getEventSchedule;
//		$events = $this->module->events;
//
//		$this->assert
//		Craft::$app->setEdition(Craft::Pro);
//
//		$this->assertSame(
//			Craft::Pro,
//			Craft::$app->getEdition());
	}

//	getEventSchedule
//	getEventGroupSchedule
//	addAndUpdateSchedules
//	deleteScheduleByIds
//	getAllSchedules

}
