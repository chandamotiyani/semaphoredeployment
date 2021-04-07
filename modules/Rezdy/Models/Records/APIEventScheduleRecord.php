<?php


namespace modules\Rezdy\Models\Records;


use craft\db\ActiveRecord;

class APIEventScheduleRecord extends ActiveRecord {

	public static function tableName(): string
	{
		return '{{%yalumba_api_event_schedule}}';
	}

}