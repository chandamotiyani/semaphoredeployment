<?php


namespace modules\Orders;



use yii\base\Behavior;
use Craft;

class OrderStatusVariableBehavior extends Behavior {
	public function orderStatus() {
		return OrdersModule::getInstance()->orderStatus;
	}
}