<?php


namespace modules\FreeShipping;


use craft\commerce\elements\Order;
use craft\commerce\models\OrderAdjustment;
use craft\commerce\services\OrderAdjustments;
use craft\commerce\adjusters\Shipping;
use craft\events\RegisterComponentTypesEvent;
use yii\base\Event;
use \Craft;

class FreeShippingModule extends \yii\base\Module {

	/**
	 * FreeShippingModule constructor.
	 */
	public function __construct($id, $parent = null, array $config = []){
		Craft::setAlias('@modules/FreeShipping', $this->getBasePath());
		//$this->controllerNamespace = 'modules\GiftOptions\Controllers';

		parent::__construct($id, $parent, $config);
	}

	public function init() {
		parent::init();
		Event::on( OrderAdjustments::class, OrderAdjustments::EVENT_REGISTER_ORDER_ADJUSTERS, function ( RegisterComponentTypesEvent $event ) {

			//$event->types[] = FreeShipping::class;
			$adjusters = $event->types;

			foreach ($adjusters as $key => $adjuster) {
				if ($adjuster == Shipping::class) {
					$adjusters[$key] = FreeShippingAdjuster::class;
				}
			}

			$event->types = $adjusters;
		} );
	}

}