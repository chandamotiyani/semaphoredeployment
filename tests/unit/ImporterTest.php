<?php

namespace Tests\unit;

use Codeception\Test\Unit;

use modules\Importer\Errors\ImportException;
use modules\Importer\ImporterModule;
use modules\Importer\Services\Importer;
use UnitTester;
use Craft;

class ImporterTest extends Unit
{
	/**
	 * @var UnitTester
	 */
	protected $tester;

	protected $module;

	private $importerKeys = [
		'events',
		'event-schedule',
		'products',
		'orders',
		'line-items',
	];

	private $invalidKeys = [
		'',
		'events ',
		'eventschedule',
		'event schedule'
	];

	public function _before(){
		$this->module = ImporterModule::getInstance();
	}

	public function testServiceValid()
	{
		$this->assertInstanceOf(Importer::class, $this->module->importer);
	}

	public function testDefaultImportClassSetup()
	{
		$importers = $this->module->importer->getKeyedImporters();

		$this->assertIsArray($importers);
		foreach($this->importerKeys as $importer_key){
			$this->assertArrayHasKey($importer_key, $importers);
		}
	}


	/**
	 * @param $key
	 * @dataProvider validImporterKeyProvider
	 */
	public function testSpecificImportClassSetup($key)
	{
		$this->module->importer->setImporters($key);
		$importers = $this->module->importer->getKeyedImporters();
		$this->assertArrayHasKey($key, $importers);
	}

	/**
	 * @param $key
	 * @dataProvider invalidImporterKeyProvider
	 */
	public function testInvalidSpecificImportClassSetup($key)
	{
		$this->expectException(ImportException::class);
		$this->module->importer->setImporters($key);
		$importers = $this->module->importer->getKeyedImporters();
	}

	/**
	 * provides invalid importer keys
	 * @return array
	 */
	public function invalidImporterKeyProvider(){
		$items = [];

		foreach($this->invalidKeys as $importer_key){
			$items[] = [$importer_key];
		}
		return $items;
	}

	/**
	 * provides valid importer keys
	 * @return array
	 */
	public function validImporterKeyProvider(){
		$items = [];
		foreach($this->importerKeys as $importer_key){
			$items[] = [$importer_key];
		}
		return $items;
	}

	/**
	 * @param $key
	 * @dataProvider validImporterKeyProvider
	 */
	public function testImporters($key){
		$this->module->importer->setImporters($key);
		$this->module->importer->import();
	}
}
