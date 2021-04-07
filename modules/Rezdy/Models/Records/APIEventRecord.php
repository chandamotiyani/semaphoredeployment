<?php


namespace modules\Rezdy\Models\Records;


use craft\db\ActiveRecord;

class APIEventRecord extends ActiveRecord {

	public static function tableName(): string
	{
		return '{{%yalumba_api_events}}';
	}

}