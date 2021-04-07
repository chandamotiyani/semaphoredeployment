<?php


namespace modules\Orders\Services;

use craft\base\Component;
use craft\commerce\elements\Order;
use craft\commerce\models\Customer;
use craft\commerce\Plugin as Commerce;
use modules\Orders\Models\LineItemModel;
use modules\Orders\Models\Records\LineItemRecord;
use modules\Orders\Models\OrderModel;
use modules\Orders\Models\Records\OrderRecord as OrderRecord;

class Orders extends Component {

	private $siteOrders;
	private $_orders = [];
	private $_order = [];
	private $_customer_orders = [];
	private $_lineItems = NULL;
	/**
	 * Orders constructor.
	 */
	public function __construct() {
		$this->siteOrders = Commerce::getInstance()->getOrders();
	}

	public function getYalumbaLineItems($yalumbaOrderId){

		$orderRecord = OrderRecord::find()->where(['orderNumber'=>$yalumbaOrderId])->one();
		if($this->_lineItems === NULL){
			foreach($orderRecord->lineItems as $lineItemRecord){
				$lineItem              = new LineItemModel();
                $lineItem->id          = $lineItemRecord->id;
				$lineItem->orderNumber = $lineItemRecord->orderNumber;
				$lineItem->lineNumber  = $lineItemRecord->lineNumber;
				$lineItem->phonetic    = $lineItemRecord->phonetic;
				$lineItem->productName = $lineItemRecord->productName;
				$lineItem->quantity    = $lineItemRecord->quantity;
				$lineItem->price       = $lineItemRecord->price;
				$lineItem->status      = $lineItemRecord->status;
				$this->_lineItems[]    =  $lineItem;
			}
		}
		return $this->_lineItems;
	}

	public function getYalumbaOrder(string $orderNumber){
		$record = OrderRecord::find()->where(['orderId'=>$orderNumber])->one();
		$this->_order = new OrderModel($record->toArray([
			'id',
			'orderNumber',
			'accountNumber',
			'accountName',
			'orderDate',
			'orderId',
			'status',
			'totalPrice',
			'totalQuantity'
		]));
		return $this->_order;
	}

	public function getUserYalumbaOrderByOrderId($orderId, $user){
		$customer = Commerce::getInstance()->customers->getCustomerByUserId($user->id);
		$record = OrderRecord::find()->where(['orderId'=>$orderId])
		                             ->where(['accountNumber'=>$customer->id])
		                             ->one();
		$this->_order = new OrderModel($record->toArray([
			'id',
			'orderNumber',
			'accountNumber',
			'accountName',
			'orderDate',
			'orderId',
			'status',
			'totalPrice',
			'totalQuantity'
		]));
		return $this->_order;
	}
	
	public function getYalumbaOrdersByCustomer(Customer $customer){
        if(!$this->_customer_orders) {
            $records = OrderRecord::find()->where(['accountNumber'=>$customer->id])->orderBy('dateCreated DESC')->all();

            foreach ($records as $record) {
                $this->_customer_orders[] = new OrderModel(
                    $record->toArray(
                        [
                            'id',
                            'orderNumber',
                            'accountNumber',
                            'accountName',
                            'orderDate',
                            'orderId',
                            'status',
                            'totalPrice',
                            'totalQuantity'
                        ]
                    )
                );
            }
        }
		return $this->_customer_orders;
	}

	public function pushYalumbaOrderRecord(OrderModel $order){
		if($order->id){
			$record = OrderRecord::findOne($order->id);
		}
		else{
			$record = new OrderRecord();
		}
		$record->orderNumber = $order->orderNumber;
		$record->accountNumber = $order->accountNumber;
		$record->accountName = $order->accountName;
		$record->orderDate = $order->orderDate;
		$record->orderNumber = $order->orderNumber;
		$record->orderId = $order->orderId;
		$record->status = $order->status;
		$record->identifier = $order->identifier;
		$record->totalQuantity = $order->totalQuantity;
		$record->totalPrice = $order->totalPrice;
		$record->save();
		return $order;
	}


}