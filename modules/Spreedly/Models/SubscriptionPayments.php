<?php


namespace modules\Spreedly\Models;


use craft\base\Model;
use craft\commerce\models\subscriptions\SubscriptionPayment as Payment;
use craft\validators\HandleValidator;
use craft\validators\UniqueValidator;

class SubscriptionPayment extends Payment {
	public $subscriptionId;
}