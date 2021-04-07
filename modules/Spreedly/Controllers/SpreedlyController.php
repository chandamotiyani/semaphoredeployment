<?php


namespace modules\Spreedly\Controllers;


use craft\web\Controller;
use modules\Spreedly\SpreedlyModule;

class SpreedlyController extends Controller {

	/**
	 * seems like we need to create a test spreedly gateway using the api...
	 */
	public function actionCreateTest(){
		$r = SpreedlyModule::getInstance()->spreedly_api->createTestGateway();
		echo('<pre>');
		var_dump($r);
		exit();
	}

	/**
	 * gets the supported gateways.
	 */
	public function actionSupportedGateways(){
		$r = SpreedlyModule::getInstance()->spreedly_api->getSupportedGateways();
		echo('<pre>');
		var_dump($r);
		exit();
	}
}

