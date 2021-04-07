<?php


namespace modules\Orders;



use yii\base\Behavior;
use Craft;

class OrdersVariableBehavior extends Behavior {
	public function yalumbaOrders() {
		return OrdersModule::getInstance()->orders;
	}
}