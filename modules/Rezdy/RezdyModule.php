<?php
/**
 * User: sidavies
 * Date: 2019-07-31
 * Time: 15:38
 * File: Login.php
 */

namespace modules\Rezdy;


use Craft;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterTemplateRootsEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\log\FileTarget;
use craft\services\Fields;
use craft\web\UrlManager;
use craft\web\View;
use modules\Rezdy\Fields\RezdyId;
use modules\Rezdy\Services\RezdyApiService;
use yii\base\Event;
use yii\base\Module;

class RezdyModule extends Module {

	public static $instance;
	public $hasCpSettings = true;

	public function __construct($id, $parent = null, array $config = []) {

		Craft::setAlias('@modules/Rezdy', $this->getBasePath());
		$this->controllerNamespace = 'modules\Rezdy\Controllers';

		// Set this as the global instance of this module class
		static::setInstance($this);
		parent::__construct($id, $parent, $config);

	}

	protected function settingsHtml()
	{
		return \Craft::$app->getView()->renderTemplate('rezdy/settings', [
			'settings' => $this->getSettings()
		]);
	}

	public function init(){
		parent::init();

		$this->setComponents([
			'api' => RezdyApiService::class,
		]);

		// Register our CP routes
		Event::on(
			UrlManager::class,
			UrlManager::EVENT_REGISTER_CP_URL_RULES,
			function (RegisterUrlRulesEvent $event) {
				$event->rules = array_merge($event->rules,$this->cpRoutes());
			}
		);

        Craft::getLogger()->dispatcher->targets[] = new FileTarget([
            'logFile' => '@storage/logs/rezdy_api.log', // <--- path of the log file
            'categories' => ['rezdy_api'=>RezdyApiService::class] // <--- categories in the file
        ]);

		// Base template directory
		Event::on(View::class, View::EVENT_REGISTER_CP_TEMPLATE_ROOTS, function (RegisterTemplateRootsEvent $e) {
			if (is_dir($baseDir = $this->getBasePath().DIRECTORY_SEPARATOR.'templates')) {
				$e->roots[$this->id] = $baseDir;
			}
		});

		Event::on(Fields::class, Fields::EVENT_REGISTER_FIELD_TYPES, function(RegisterComponentTypesEvent $event) {
			$event->types[] = RezdyId::class;
		});
	}

	public function cpRoutes() {
		return [
//			'/rezdy' => 'rezdy/rezdy/index',
		];
	}
}