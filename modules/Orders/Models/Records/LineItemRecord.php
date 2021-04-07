<?php


namespace modules\Orders\Models\Records;


use craft\commerce\records\Order;
use craft\db\ActiveRecord;

class LineItemRecord extends ActiveRecord {
	public static function tableName() {
		return '{{%yalumba_line_items}}';
	}

	public function getOrders(){
		return $this->hasOne(Order::class, ['orderNumber'=>'orderNumber']);
	}
}