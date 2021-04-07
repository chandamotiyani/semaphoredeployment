<?php


namespace modules\Orders\Models;


use craft\base\Model;
use modules\Orders\OrdersModule;

class LineItemModel extends Model {
	public $orderNumber;
	public $lineNumber;
	public $phonetic;
	public $productName;
	public $quantity;
	public $price;
	public $status;
    public $id;

	private $_order;

	public function getOrder(){
		if(null === $this->_order){
			// TODO: Why isn't there a N+1 safe way of handling this?
			// other methods use the serves to select something in here - is it possible
			// this saves duplicate queries - no service method exists to get a subscription
			// anyways - can I maybe create one?
			$this->_order = OrdersModule::getInstance()->orders->getYalumbaOrder($this->orderNumber);
		}
		return $this->_order;
	}
}