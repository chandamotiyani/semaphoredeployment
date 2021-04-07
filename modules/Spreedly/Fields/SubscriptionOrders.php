<?php


namespace modules\Spreedly\Fields;


use craft\base\ElementInterface;
use craft\base\Field;
use craft\helpers\Html;
use \Craft;
use craft\models\FieldLayout;
use modules\Spreedly\Models\SubscriptionOrder;
use modules\Spreedly\SpreedlyModule;

class SubscriptionOrders extends Field{

	public static function displayName(): string {
		return 'Subscription Orders';
	}

	public static function hasContentColumn(): bool
	{
		return false;
	}

	public function getInputHtml($value, ElementInterface $element = null): string
	{
		$orders = SpreedlyModule::getInstance()->subscription_orders->getSubscriptionOrdersBySubscriptionId($element->id);

		return Craft::$app->getView()->renderTemplate('spreedly/_orders',['orders'=>$orders]);

	}
}