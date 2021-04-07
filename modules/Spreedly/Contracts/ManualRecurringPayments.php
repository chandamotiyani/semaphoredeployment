<?php


namespace modules\Spreedly\Contracts;


use craft\commerce\base\Gateway;
use craft\commerce\base\Plan;
use craft\commerce\base\Purchasable;
use craft\commerce\elements\Subscription;
use modules\Spreedly\Models\Payment;
use modules\Spreedly\Models\PaymentSource;

interface ManualRecurringPayments {
	public function makeRecurringPayment(\craft\commerce\models\PaymentSource $paymentSource, Payment $payment);
}