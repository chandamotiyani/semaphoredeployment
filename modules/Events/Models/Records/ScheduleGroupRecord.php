<?php


namespace modules\Events\Models\Records;


use craft\base\Model;
use craft\db\ActiveRecord;
use craft\db\SoftDeleteTrait;
use craft\records\FieldLayout;
use modules\Events\Elements\Event;
use yii\db\ActiveQueryInterface;

/**
 *
 * @property ActiveQueryInterface $events
 */
class ScheduleGroupRecord extends ActiveRecord {
	// Traits
	// =========================================================================

	use SoftDeleteTrait;

	// Public Methods
	// =========================================================================


	/**
	 * Returns the event group’s fieldLayout.
	 *
	 * @return ActiveQueryInterface The relational query object.
	 */
	public function getFieldLayout(): ActiveQueryInterface
	{
		return $this->hasOne(FieldLayout::class,
			['id' => 'fieldLayoutId']);
	}

	/**
	 * @inheritdoc
	 * @return string
	 */
	public static function tableName(): string
	{
		return '{{%yalumba_eventschedulegroup}}';
	}
}