<?php


namespace modules\ApiLog;


use craft\events\RegisterTemplateRootsEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\web\UrlManager;
use craft\web\View;
use modules\ApiLog\Services\ApiLog;
use yii\base\Event;
use yii\base\Module;
use \Craft;

class ApiLogModule extends Module {

	public function __construct($id, $parent = null, array $config = []) {
		Craft::setAlias('@modules/ApiLog', $this->getBasePath());
		$this->controllerNamespace = 'modules\ApiLog\Controllers';
		// Set this as the global instance of this module class
		static::setInstance($this);
		parent::__construct($id, $parent, $config);
	}

	public function init(){
		parent::init();

		$this->setComponents([
			'logger' => ApiLog::class,
		]);

        // Register our CP routes
        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_CP_URL_RULES,
            function (RegisterUrlRulesEvent $event) {
                $event->rules = array_merge($event->rules,$this->cpRoutes());
            }
        );


        // Base template directory
        Event::on(View::class, View::EVENT_REGISTER_CP_TEMPLATE_ROOTS, function (RegisterTemplateRootsEvent $e) {
            if (is_dir($baseDir = $this->getBasePath().DIRECTORY_SEPARATOR.'templates')) {
                $e->roots[$this->id] = $baseDir;
            }
        });

	}


    public function cpRoutes() {
        return [
            '/log/<elementId:\d+>' => 'api-log/log/view',
            '/importer/' => 'importer/importer/index',
        ];
    }
}