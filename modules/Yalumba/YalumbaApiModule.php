<?php


namespace modules\Yalumba;


use craft\log\FileTarget;
use modules\SiteModule\Services\ProductService;
use modules\Yalumba\Services\YalumbaApi;
use yii\base\Module;
use \Craft;

class YalumbaApiModule extends Module {

	public function __construct($id, $parent = null, array $config = []) {
		Craft::setAlias('@modules/Yalumba', $this->getBasePath());
		$this->controllerNamespace = 'modules\Yalumba\Controllers';
		// Set this as the global instance of this module class
		static::setInstance($this);
		parent::__construct($id, $parent, $config);
	}

	public function init(){
		parent::init();

        // include the new target file target to the dispatcher
        Craft::getLogger()->dispatcher->targets[] = new FileTarget([
            'logFile' => '@storage/logs/yalumba_api.log', // <--- path of the log file
            'categories' => ['yalumba_api'=>YalumbaApi::class] // <--- categories in the file
        ]);


		$this->setComponents([
			'api' => YalumbaApi::class,
			'products' => ProductService::class,
		]);
	}

}