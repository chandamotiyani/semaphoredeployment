<?php
/**
 * @link https://craftcms.com/
 * @copyright Copyright (c) Pixel & Tonic, Inc.
 * @license https://craftcms.github.io/license/
 */

namespace modules\Spreedly\Models\Records;

use craft\db\ActiveRecord;
use craft\db\SoftDeleteTrait;
use craft\db\Table;
use craft\records\FieldLayout;
use yii\db\ActiveQueryInterface;

/**
 * Class EventGroup record.
 *
 * @property int $id ID
 * @property int $fieldLayoutId Field layout ID
 * @property string $name Name
 * @property string $handle Handle
 * @property FieldLayout $fieldLayout Field layout
 * @property SubscriptionPayment[] $events Events
 */
class SubscriptionPayment extends ActiveRecord
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
		return '{{%subscription_payments}}';
	}

}
