<?php
namespace modules\Spreedly\Services;

use craft\base\Component;
use craft\helpers\ArrayHelper;
use modules\Spreedly\Models\Records\SubscriptionOrder as SubscriptionOrderRecord;
use modules\Spreedly\Models\SubscriptionOrder as SubscriptionOrderModel;

class SubscriptionOrders extends Component {
	private $_subscriptionOrders;
	public function getSubscriptionOrdersBySubscriptionId(int $subscriptionId) {
		$records = SubscriptionOrderRecord::find()
					->where(['subscriptionId'=>$subscriptionId])
		                                  ->all();
		foreach ($records as $record) {
			$this->_subscriptionOrders[] = new SubscriptionOrderModel($record->toArray([
				'id',
				'userId',
				'shippingAddressId',
				'dateCreated',
				'paymentAmount',
				'paymentCurrency',
				'subscriptionId',
				'paymentSourceId',
			]));
		}
		return $this->_subscriptionOrders;
	}

	public function create($model){
		$record = new SubscriptionOrderRecord();
		$record->subscriptionId = $model->subscriptionId;
		$record->userId = $model->userId;
		$record->shippingAddressId = $model->shippingAddressId;
		$record->paymentSourceId = $model->paymentSourceId;
		$record->paymentAmount = $model->paymentAmount;
		$record->paymentCurrency = $model->paymentCurrency;
		$record->save();
		return new SubscriptionOrderModel($record->toArray([
			'id',
			'subscriptionId',
			'userId',
			'shippingAddressId',
			'paymentSourceId',
			'paymentAmount',
			'paymentCurrency',
		]));
	}
}