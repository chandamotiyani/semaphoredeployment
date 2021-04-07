<?php
namespace modules\Importer\Controllers;

use craft\web\Controller;
use modules\Importer\ImporterModule;
use modules\Importer\Importers\EventScheduleImporter;
use modules\Importer\Importers\EventsImporter;
use modules\Importer\Importers\LineItemsImporter;
use modules\Importer\Importers\OrdersImporter;
use modules\Importer\Importers\ProductsImporter;

/**
 * Setup Controller
 *
 * Generally speaking, controllers are the middlemen between the front end of
 * the CP/website and your plugin’s services. They contain action methods which
 * handle individual tasks.
 *
 * A common pattern used throughout Craft involves a controller action gathering
 * post data, saving it on a model, passing the model off to a service, and then
 * responding to the request appropriately depending on the service method’s response.
 *
 * Action methods begin with the prefix “action”, followed by a description of what
 * the method does (for example, actionSaveIngredient()).
 *
 * https://craftcms.com/docs/plugins/controllers
 *
 * @author    tretrewt
 * @package   Rezdy
 * @since     treytre
 */
class ImporterController extends Controller{
	private $importers;


	/**
	 * Pass in an importer to run.
	 * @param string $itemToImport
	 */
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

}