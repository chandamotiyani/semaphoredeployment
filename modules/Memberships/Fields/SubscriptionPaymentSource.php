<?php


namespace modules\Memberships\Fields;


use craft\base\ElementInterface;
use craft\base\Field;
use craft\commerce\Plugin as Commerce;

class SubscriptionPaymentSource extends Field{
	public static function displayName(): string {
		return 'Subscription Payment Source';
	}

	public function getInputHtml($value, ElementInterface $element = null): string
	{
		if($value){
			$paymentSource = Commerce::getInstance()->getPaymentSources()->getPaymentSourceByIdAndUserId($value, $element->id);
		}
		else{
			$paymentSource = false;
		}
		return \Craft::$app->getView()->renderTemplate('memberships/_payment_source',['paymentSource'=>$paymentSource]);
	}
}