<?php
namespace modules\ApiLog\Controllers;

use craft\web\Controller;
use modules\ApiLog\ApiLogModule;

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
 * @package   ApiLog
 */
class LogController extends Controller{



	/**
	 * Pass in an importer to run.
	 * @param string $itemToImport
	 */
	public function actionView(int $elementId){
	    $logs = ApiLogModule::getInstance()->logger->getLogByElement($elementId);

        return $this->renderTemplate('api-log/view', [
            'logs'=>$logs
        ]);
	}

}