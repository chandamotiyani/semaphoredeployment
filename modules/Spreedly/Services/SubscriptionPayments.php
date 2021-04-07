<?php


namespace modules\Spreedly\Services;


use modules\Spreedly\Models\Records\SubscriptionPayment;
use modules\Spreedly\Models\Records\SubscriptionPayment as SubscriptionPaymentRecord;
use yii\base\Component;

class SubscriptionPayments extends Component {

	private $_subscriptionPayments = [];

	public function getSubscriptionPayments($subscriptionId){
		$records = SubscriptionPaymentRecord::find()->where(['subscriptionId'=>$subscriptionId])->all();
		foreach ($records as $record) {

			$this->_subscriptionPayments[] = new \modules\Spreedly\Models\SubscriptionPayment($record->toArray([
				'subscriptionId',
				'paymentAmount',
				'paymentCurrency',
				'paymentDate',
				'paymentReference',
				'paid',
				'response',
			]));
		}
		return $this->_subscriptionPayments;
	}

	public function create($model){
		$record = new SubscriptionPayment();
		$record->subscriptionId = $model->subscriptionId;
		$record->paymentAmount = $model->paymentAmount;
		$record->paymentCurrency = $model->paymentCurrency;
		$record->paymentDate = $model->paymentDate;
		$record->paymentReference = $model->paymentReference;
		$record->paid = $model->paid;
		$record->response = $model->response;
		$record->save();
		return new \craft\commerce\models\subscriptions\SubscriptionPayment($record->toArray([
			'paymentAmount',
			'paymentCurrency',
			'paymentDate',
			'paymentReference',
			'paid',
			'response',
		]));
	}
}