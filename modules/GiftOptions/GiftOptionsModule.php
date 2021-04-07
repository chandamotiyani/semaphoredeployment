<?php


namespace modules\GiftOptions;


use craft\commerce\elements\Order;
use craft\commerce\models\OrderAdjustment;
use craft\commerce\services\OrderAdjustments;
use craft\events\RegisterComponentTypesEvent;
use yii\base\Event;
use \Craft;

class GiftOptionsModule extends \yii\base\Module {

	/**
	 * GiftOptionsModule constructor.
	 */
	public function __construct($id, $parent = null, array $config = []){
		Craft::setAlias('@modules/GiftOptions', $this->getBasePath());
		//$this->controllerNamespace = 'modules\GiftOptions\Controllers';

		parent::__construct($id, $parent, $config);
	}

	public function init() {
		parent::init();
		Event::on( OrderAdjustments::class, OrderAdjustments::EVENT_REGISTER_ORDER_ADJUSTERS, function ( RegisterComponentTypesEvent $event ) {

			$event->types[] = GiftAdjuster::class;
		} );
	}

}