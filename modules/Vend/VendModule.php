<?php


namespace modules\Vend;


use craft\log\FileTarget;
use modules\Vend\Services\VendApi;
use yii\base\Module;
use \Craft;

class VendModule extends Module {

	public function __construct($id, $parent = null, array $config = []) {
		Craft::setAlias('@modules/Vend', $this->getBasePath());
		$this->controllerNamespace = 'modules\Vend\Controllers';
		// Set this as the global instance of this module class
		static::setInstance($this);
		parent::__construct($id, $parent, $config);
	}

	public function init(){
		parent::init();

        // include the new target file target to the dispatcher
        Craft::getLogger()->dispatcher->targets[] = new FileTarget([
           'logFile' => '@storage/logs/vend.log', // <--- path of the log file
           'categories' => ['vend_api'=>VendApi::class] // <--- categories in the file
       ]);

		$this->setComponents([
			'api' => VendApi::class,
		]);
	}
}