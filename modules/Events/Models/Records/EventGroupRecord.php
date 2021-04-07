<?php
/**
 * @link https://craftcms.com/
 * @copyright Copyright (c) Pixel & Tonic, Inc.
 * @license https://craftcms.github.io/license/
 */

namespace modules\Events\Models\Records;

use craft\db\ActiveRecord;
use craft\db\SoftDeleteTrait;
use craft\db\Table;
use craft\records\FieldLayout;
use modules\Events\Elements\Event;
use yii\db\ActiveQueryInterface;

/**
 * Class EventGroup record.
 *
 * @property int $id ID
 * @property int $fieldLayoutId Field layout ID
 * @property string $name Name
 * @property string $handle Handle
 * @property FieldLayout $fieldLayout Field layout
 * @property Event[] $events Events
 */
class EventGroupRecord extends ActiveRecord
{
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
		return '{{%yalumba_eventgroups}}';
	}

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
	 * Returns the event group’s events.
	 *
	 * @return ActiveQueryInterface The relational query object.
	 */
	public function getEvents(): ActiveQueryInterface
	{
		return $this->hasMany(EventRecord::class, [ 'groupId' => 'id']);
	}
}
