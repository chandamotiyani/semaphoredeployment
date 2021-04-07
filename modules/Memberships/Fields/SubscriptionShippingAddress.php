<?php


namespace modules\Memberships\Fields;


use craft\base\ElementInterface;
use craft\base\Field;
use craft\commerce\Plugin as Commerce;

class SubscriptionShippingAddress extends Field{
	public static function displayName(): string {
		return 'Subscription Shipping Address';
	}

	public function getInputHtml($value, ElementInterface $element = null): string
	{
		$customer = Commerce::getInstance()->getCustomers()->getCustomerByUserId($element->id);
		//$addresses = Commerce::getInstance()->getAddresses()->getAddressesByCustomerId($customer->id);
		if($value){
			$address = Commerce::getInstance()->getAddresses()->getAddressByIdAndCustomerId($value, $customer->id);
		}
		else{
			$address = false;
		}
		return \Craft::$app->getView()->renderTemplate('memberships/_shipping_address',['address'=>$address]);
	}
}