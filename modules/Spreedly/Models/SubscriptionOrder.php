<?php


namespace modules\Spreedly\Models;


use craft\base\Model;
use craft\commerce\elements\Subscription;
use craft\commerce\Plugin as Commerce;

class SubscriptionOrder extends Model {
	public $id;
	public $userId;
	public $shippingAddressId;
	public $dateCreated;
	public $subscriptionId;
	public $paymentSourceId;
	public $paymentAmount;
	public $paymentCurrency;
	public $_subscription;

	public function getSubscription(){
		if(null === $this->_subscription){
			// TODO: Why isn't there a N+1 safe way of handling this?
			// other methods use the serves to select something in here - is it possible
			// this saves duplicate queries - no service method exists to get a subscription
			// anyways - can I maybe create one?
			$this->_subscription = Subscription::findOne($this->subscriptionId);
		}
		return $this->_subscription;
	}
//
//	public function getGateway()
//	{
//		if (null === $this->_gateway) {
//			$this->_gateway = Commerce::getInstance()->getGateways()->getGatewayById($this->gatewayId);
//		}
//
//		return $this->_gateway;
//	}
}