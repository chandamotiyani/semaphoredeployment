<?php

namespace craft\contentmigrations;

use Craft;
use craft\db\Migration;
use craft\db\mysql\Schema;

/**
 * m191211_044507_addEventGroupHandleField migration.
 */
class m191211_044507_addEventGroupHandleField extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
	    $this->addColumn('{{%events}}','groupHandle', Schema::TYPE_STRING);
	    $this->dropColumn('{{%events}}', 'eventType');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m191211_044507_addEventGroupHandleField cannot be reverted.\n";
        return false;
    }
}
