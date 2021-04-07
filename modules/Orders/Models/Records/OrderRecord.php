<?php


namespace modules\Orders\Models\Records;


use craft\db\ActiveRecord;

class OrderRecord extends ActiveRecord {
	public static function tableName(): string {
		return '{{%yalumba_orders}}';
	}

	public function getOrder(){
		return $this->hasOne(\craft\commerce\records\Order::class, ['number'=>'orderId']);
	}

	public function getLineItems(){
		return $this->hasMany(LineItemRecord::class, ['orderNumber'=>'orderNumber']);
	}
}