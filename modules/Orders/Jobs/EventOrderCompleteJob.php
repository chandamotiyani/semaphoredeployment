<?php


namespace modules\Yalumba\Jobs;


use craft\commerce\Plugin as Commerce;
use craft\queue\JobInterface;
use craft\queue\QueueInterface;
use modules\Events\EventsModule;
use modules\Orders\Models\OrderModel;
use modules\Orders\OrdersModule;
use modules\Rezdy\RezdyModule;
use modules\Yalumba\YalumbaApiModule;
use yii\base\BaseObject;

class EventOrderCompleteJob extends BaseObject implements JobInterface {

	public $orderId;
	public $lineItemId;

	public function getDescription() {
		return "Send Event Complete to Api";
	}

	public function execute( $queue ) {
		$order = Commerce::getInstance()->orders->getOrderById($this->orderId);
		$lineItem = Commerce::getInstance()->lineItems->getLineItemById($this->lineItemId);
		EventsModule::getInstance()->purchase->complete($order,$lineItem);
	}
}