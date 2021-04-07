<?php


namespace modules\Orders\Models;


use craft\base\Model;
use craft\commerce\Plugin as Commerce;
use craft\commerce\records\Customer;
use modules\Orders\OrdersModule;

class ProductModel extends Model {
	public $orderNumber;
	public $accountNumber;
	public $accountName;
	public $orderDate;
	public $orderId;
	public $status;
	public $id;
	public $totalQuantity;
	public $totalPrice;

	private $_customer;
	private $_order;
	private $_lineItems;

	public function getOrder(){
		if(null === $this->_order){
			// TODO: Why isn't there a N+1 safe way of handling this?
			// other methods use the serves to select something in here - is it possible
			// this saves duplicate queries - no service method exists to get a subscription
			// anyways - can I maybe create one?
			$this->_order = \craft\commerce\elements\Order::findOne($this->orderId);
		}

		return $this->_order;
	}

	public function getCustomer(){
		if(null === $this->_customer){
			// TODO: Why isn't there a N+1 safe way of handling this?
			// other methods use the serves to select something in here - is it possible
			// this saves duplicate queries - no service method exists to get a subscription
			// anyways - can I maybe create one?
			$this->_customer = Commerce::getInstance()->getCustomers()->getCustomerById($this->accountNumber);
		}
		return $this->_customer;
	}

	public function getLineItems(){
		if(null === $this->_lineItems){
			// TODO: Why isn't there a N+1 safe way of handling this?
			// other methods use the serves to select something in here - is it possible
			// this saves duplicate queries - no service method exists to get a subscription
			// anyways - can I maybe create one?
			$this->_lineItems = OrdersModule::getInstance()->orders->getYalumbaLineItems($this->orderNumber);
		}
		return $this->_lineItems;
	}
}