<?php


namespace modules\Spreedly\Models;



use craft\commerce\models\payments\CreditCardPaymentForm;
use craft\commerce\models\PaymentSource;
use modules\Spreedly\SpreedlyModule;

class PaymentForm extends CreditCardPaymentForm {

	public $cardReference;
	public $useForMemberPayment;

	/**
	 * @inheritdoc
	 */
	public function populateFromPaymentSource(PaymentSource $paymentSource)
	{
		$this->cardReference = $paymentSource->token;
	}

	public function rules()
	{
		return [
			[['firstName', 'lastName', 'month', 'year', 'cvv', 'number'], 'required', 'when'=>function($model){
				return !$model->cardReference;
			}],
			[['month'], 'integer', 'integerOnly' => true, 'min' => 1, 'max' => 12],
			[['year'], 'integer', 'integerOnly' => true, 'min' => date('Y'), 'max' => date('Y') + 12],
			[['cvv'], 'integer', 'integerOnly' => true],
			[['cvv'], 'string', 'length' => [3, 4]],
			[['number'], 'integer', 'integerOnly' => true],
			[['number'], 'string', 'max' => 19],
			[['number'], 'creditCardLuhn']
		];
	}
}