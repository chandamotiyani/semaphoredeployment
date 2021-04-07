<?php


namespace modules\ApiLog\Models\Records;


use craft\commerce\records\Order;
use craft\db\ActiveRecord;

class ApiLogRecord extends ActiveRecord {
	public static function tableName() {
		return '{{%yalumba_api_log}}';
	}

	public function getOrders(){
		return $this->hasOne(Order::class, ['orderNumber'=>'orderNumber']);
	}
}