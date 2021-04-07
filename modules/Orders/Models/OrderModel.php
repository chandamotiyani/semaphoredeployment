<?php


namespace modules\Orders\Models;


use craft\base\Model;
use craft\commerce\Plugin as Commerce;
use craft\commerce\records\Customer;
use modules\Orders\OrdersModule;

class OrderModel extends Model {
	public $orderNumber;
	public $accountNumber;
	public $accountName;
	public $orderDate;
	public $orderId;

	public $status;
	public $id;
	public $totalQuantity;
	public $totalPrice;
	public $identifier;

	private $_customer;
	private $_order;
	private $_lineItems;

	public function getOrder(){
		if(null === $this->_order) {
			//$this->_order = \craft\commerce\elements\Order::find()->where('number'One($this->orderId);
            // Chanda - use OrderNumber if orderId is null to combat the issue
            $orderId = null === $this->orderId ? $this->orderNumber : $this->orderId;
			$this->_order = Commerce::getInstance()->orders->getOrderByNumber($orderId);
		}

		return $this->_order;
	}

	public function getCustomer(){
		if(null === $this->_customer){
			$this->_customer = Commerce::getInstance()->getCustomers()->getCustomerById($this->accountNumber);
		}
		return $this->_customer;
	}

	public function getLineItems(){
		if(null === $this->_lineItems){
			$this->_lineItems = OrdersModule::getInstance()->orders->getYalumbaLineItems($this->orderNumber);
		}
		return $this->_lineItems;
	}
}