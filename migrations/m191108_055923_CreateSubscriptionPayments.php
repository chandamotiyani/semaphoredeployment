<?php

namespace craft\contentmigrations;

use Craft;
use craft\db\Migration;
use craft\db\mysql\Schema;

/**
 * m191108_055923_CreaetSubscriptionPayments migration.
 */
class m191108_055923_CreateSubscriptionPayments extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
	    $this->dropTableIfExists('{{%subscription_payments}}');
	    $this->createTable('{{%subscription_payments}}', [
		    'id' => Schema::TYPE_PK,
		    'dateCreated'=>Schema::TYPE_DATETIME,
		    'dateUpdated'=>Schema::TYPE_DATETIME,
		    'dateDeleted'=>Schema::TYPE_DATETIME,
		    'subscription_id'=>Schema::TYPE_INTEGER,
		    'paymentAmount'=>Schema::TYPE_FLOAT,
		    'paymentCurrency'=>Schema::TYPE_STRING,
		    'paymentDate'=>Schema::TYPE_DATETIME,
		    'paymentReference'=>Schema::TYPE_STRING,
		    'paid'=>Schema::TYPE_BOOLEAN,
		    'response'=>Schema::TYPE_TEXT
        ]);
        // Place migration code here...
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m191108_055923_CreateSubscriptionPayments cannot be reverted.\n";
        return false;
    }
}
