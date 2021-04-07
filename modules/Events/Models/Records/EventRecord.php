<?php


namespace modules\Events\Models\Records;


use craft\db\ActiveRecord;
use craft\records\Element;
use yii\db\ActiveQueryInterface;

class EventRecord extends ActiveRecord {

	public static function tableName(): string
	{
		return '{{%yalumba_events}}';
	}

	public function getElement(): ActiveQueryInterface
	{
		return $this->hasOne(Element::class, ['id' => 'id']);
	}

	/**
	 * Returns the tag’s group.
	 *
	 * @return ActiveQueryInterface The relational query object.
	 */
	public function getGroup(): ActiveQueryInterface
	{
		return $this->hasOne(EventGroupRecord::class, [ 'id' => 'groupId']);
	}
}