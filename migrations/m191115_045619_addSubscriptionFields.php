<?php

namespace craft\contentmigrations;

use Craft;
use craft\db\Migration;
use craft\db\mysql\Schema;

/**
 * m191115_045619_addSubscriptionFields migration.
 */
class m191115_045619_addSubscriptionFields extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
	    $this->addColumn('{{%subscription_payments}}','uid', Schema::TYPE_STRING);
	    $this->addColumn('{{%subscription_orders}}','uid', Schema::TYPE_STRING);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m191115_045619_addSubscriptionFields cannot be reverted.\n";
        return false;
    }
}
