<?php

namespace Tests\unit;

use Codeception\Test\Unit;

use UnitTester;
use Craft;

class BaseTest extends Unit
{
	/**
	 * @var UnitTester
	 */
	protected $tester;

	public function testPro()
	{
		Craft::$app->setEdition(Craft::Pro);

		$this->assertSame(
			Craft::Pro,
			Craft::$app->getEdition());
	}
}
