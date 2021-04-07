<?php

namespace craft\contentmigrations;

use Craft;
use craft\db\Migration;
use craft\db\mysql\Schema;

/**
 * m191115_044335_CreateSubscriptionOrders migration.
 */
class m191115_044335_CreateSubscriptionOrders extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
	    $this->dropTableIfExists('{{%subscription_orders}}');
	    $this->createTable('{{%subscription_orders}}', [
		    'id' => Schema::TYPE_PK,
		    'dateCreated'=>Schema::TYPE_DATETIME,
		    'dateUpdated'=>Schema::TYPE_DATETIME,
		    'dateDeleted'=>Schema::TYPE_DATETIME,
			'userId' =>Schema::TYPE_INTEGER,
			'shippingAddressId' =>Schema::TYPE_INTEGER,
			'subscriptionId' =>Schema::TYPE_INTEGER,
			'paymentSourceId' =>Schema::TYPE_INTEGER,
        ]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
	    $this->dropTableIfExists('{{%subscription_orders}}');
        return false;
    }
}
