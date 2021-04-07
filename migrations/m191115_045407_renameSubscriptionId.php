<?php

namespace craft\contentmigrations;

use Craft;
use craft\db\Migration;

/**
 * m191115_045407_renameSubscriptionId migration.
 */
class m191115_045407_renameSubscriptionId extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
	    $this->renameColumn('{{%subscription_payments}}','subscription_id', 'subscriptionId');
        // Place migration code here...
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m191115_045407_renameSubscriptionId cannot be reverted.\n";
        return false;
    }
}
