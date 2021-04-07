<?php


namespace modules\Spreedly\Models\Records;

use craft\db\ActiveRecord;
use craft\db\SoftDeleteTrait;

class SubscriptionOrder extends ActiveRecord {

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
		return '{{%subscription_orders}}';
	}

	public function getSubscription(){
		return $this->hasOne(Subscription::class, ['id'=>'subscriptionId']);
	}
}