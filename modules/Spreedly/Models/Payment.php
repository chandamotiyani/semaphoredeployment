<?php


namespace modules\Spreedly\Models;

use craft\base\Model;

class Payment extends Model {
	public $gateway;
	public $paymentCurrency;
	public $paymentAmount;
	public $order;
	public $email;
	public $nextPaymentDateTime;
	public $shippingAddress;
    public $billingAddress;
}