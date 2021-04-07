<?php

namespace modules\Importer\Console\Controllers;

use \Craft;
use craft\elements\User;
use \modules\Importer\Importers\EventsImporter;
use modules\Importer\ImporterModule;
use modules\Importer\Importers\EventScheduleImporter;
use modules\Importer\Importers\LineItemsImporter;
use modules\Importer\Importers\OrdersImporter;
use modules\Importer\Importers\ProductsImporter;
use yii\console\Controller;
use yii\helpers\Console;

class ImporterController extends Controller{

	public function actionIndex(string $itemToImport=""){
		$itemsToImport = [];

		if($itemToImport){
			$itemsToImport = $this->module->importer->setImporters($itemToImport);
		}
		else{
			$itemsToImport = $this->module->importer->getKeyedImporters();
		}

		$this->module->importer->import();
	}

	public function actionCounter(){
	    $count = User::find()->vendCustomerId('')->count();
	    echo ($count);
    }

}