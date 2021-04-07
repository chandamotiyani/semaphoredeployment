<?php


namespace modules\Orders\Models\Records;


use craft\db\ActiveRecord;

class ProductRecord extends ActiveRecord {
	public static function tableName(): string {
		return '{{%yalumba_products}}';
	}

	public function getOrder(){
		return $this->hasOne(\craft\commerce\records\Order::class, ['id'=>'orderId']);
	}

	public function getLineItems(){
		return $this->hasMany(LineItemRecord::class, ['orderNumber'=>'orderNumber']);
	}
}