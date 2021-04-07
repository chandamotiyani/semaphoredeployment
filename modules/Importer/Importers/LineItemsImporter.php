<?php


namespace modules\Importer\Importers;


use craft\commerce\Plugin as Commerce;
use craft\helpers\DateTimeHelper;
use modules\Importer\Components\S3SpreadSheetImporter;
use modules\Importer\Contracts\Importer;
use modules\Orders\Models\OrderModel;
use modules\Orders\Models\ProductModel;
use modules\Orders\Models\Records\LineItemRecord;
use modules\Orders\Models\Records\OrderRecord;
use modules\Orders\Models\Records\ProductRecord;

/**
 * Import order line items from Yalumba batch export. Save this back to yalumba_line_items for use in
 * order history pages. RUN THIS AFTER RUNNING OrdersImporter
 *
 * Class LineItemsImporter
 * @package modules\Importer\Importers
 */
class LineItemsImporter extends S3SpreadSheetImporter implements Importer {

	protected static $importerIdentifier = 'line-items';
	protected $path = 'orderDetail.csv';
	protected $record = LineItemRecord::class;
	protected $skipFirst = true;
	protected $fields = [
		0=>'orderNumber',
		1=>'lineNumber',
		2=>'phonetic',
		3=>'productName',
		4=>'quantity',
		5=>'uom',
		6=>'price',
		7=>'status',
	];

//	public function getFields() {
//		return $this->fields;
//	}

	public function get_orderDate($row){
		return DateTimeHelper::toDateTime(\DateTime::createFromFormat('d-F-y', $row[3]));
	}

	public function getIdentifier($row){
		if(isset($row[0]) && isset($row[1])){
			return $row[0]."_".$row[1];
		}
		else{
			return NULL;
		}
	}

    public function after_row_save($lineItemModel){
        //order totals and qtys
        try{
            $yalumbaOrder = OrderRecord::find()->where(['orderNumber'=>$lineItemModel->orderNumber])->one();
            if($yalumbaOrder && $yalumbaOrder->orderId){
                $order = Commerce::getInstance()->getOrders()->getOrderByNumber($yalumbaOrder->orderId);
                if($order){
                    //we can use the craft order to get the totals
                    $yalumbaOrder->totalQuantity  = $order->totalQty;
                    $yalumbaOrder->totalPrice     = $order->totalPrice;
                }
                else if($yalumbaOrder){
                    //we need to calculate the order totals from the existing line items. Yes, we need to do this each time.
                    $orderLineItems = LineItemRecord::find()->where(['orderNumber'=>$lineItemModel->orderNumber])->all();
                    $qty = 0; //TODO: ignore freight;
                    $price = 0;
                    foreach($orderLineItems as $orderLineItem){
                        $qty = $qty + $orderLineItem->quantity;
                        $price = $price + ($orderLineItem->price);
                    }
                    $yalumbaOrder->totalQuantity = $qty;
                    $yalumbaOrder->totalPrice = $price;
                }
                $yalumbaOrder->save();
            }
        }
        catch(\Exception $e){
            echo('<pre>');
            var_dump($e->getMessage());
            var_dump($e->getFile());
            var_dump($e->getLine());
            exit();
        }

        return $lineItemModel;
    }
}