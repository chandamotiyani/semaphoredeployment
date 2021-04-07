<?php


namespace modules\Events\Models\Records;


use craft\base\Model;
use craft\db\ActiveRecord;
use craft\db\SoftDeleteTrait;
use modules\Events\Elements\Event;
use yii\db\ActiveQueryInterface;

/**
 *
 * @property ActiveQueryInterface $events
 */
class ScheduleRecord extends ActiveRecord {
	// Traits
	// =========================================================================

	use SoftDeleteTrait;

	// Public Methods
	// =========================================================================

	/**
	 * @inheritdoc
	 * @return string
	 */
	public static function tableName(): string
	{
		return '{{%yalumba_eventschedule}}';
	}
}