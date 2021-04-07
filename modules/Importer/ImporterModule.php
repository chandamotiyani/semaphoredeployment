<?php


namespace modules\Importer;


use craft\events\RegisterUrlRulesEvent;
use craft\log\FileTarget;
use craft\web\UrlManager;
use modules\Importer\Importers\EventScheduleImporter;
use modules\Importer\Importers\EventsImporter;
use modules\Importer\Importers\LineItemsImporter;
use modules\Importer\Importers\OrdersImporter;
use modules\Importer\Importers\ProductsImporter;
use modules\Importer\Services\Importer;
use craft\console\Application as ConsoleApplication;
use \Craft;
use yii\base\Event;
use yii\base\Module;

class ImporterModule extends Module {

	/**
	 * ImporterModule constructor.
	 *
	 * @param $id
	 * @param null $parent
	 * @param array $config
	 */
	public function __construct($id, $parent = null, array $config = []) {
		\Craft::setAlias('@modules/Importer', $this->getBasePath());
		$this->controllerNamespace = 'modules\Importer\Controllers';
		// Set this as the global instance of this module class
		static::setInstance($this);
		parent::__construct($id, $parent, $config);
	}


	public function init(){
		parent::init();

		$this->setComponents([
			'importer' => Importer::class,
		]);

		// include the new target file target to the dispatcher
		Craft::getLogger()->dispatcher->targets[] = new FileTarget([
			'logFile' => '@storage/logs/importer.log', // <--- path of the log file
			'categories' => $this->importer->getKeyedImporters() // <--- categories in the file
		]);

		if (Craft::$app instanceof ConsoleApplication) {
			$this->controllerNamespace = 'modules\Importer\Console\Controllers';
		}

		// Register our CP routes
		Event::on(
			UrlManager::class,
			UrlManager::EVENT_REGISTER_CP_URL_RULES,
			function (RegisterUrlRulesEvent $event) {
				$event->rules = array_merge($event->rules,$this->cpRoutes());
			}
		);

	}

	public function cpRoutes() {
		return [
			'/importer/<itemToImport:\w+>' => 'importer/importer/index',
			'/importer/' => 'importer/importer/index',
            '/importercount/' => 'importer/importer/counter',
		];
	}

}