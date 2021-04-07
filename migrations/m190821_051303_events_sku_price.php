<?php

namespace craft\contentmigrations;

use Craft;
use craft\db\Migration;

/**
 * m190821_051303_events_sku_price migration.
 */
class m190821_051303_events_sku_price extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
	    $this->addColumn('{{%events}}', 'sku', $this->string(255)->after('eventType'));
	    $this->addColumn('{{%events}}', 'price', $this->decimal(14,4)->after('sku'));
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m190821_051303_events_sku_price cannot be reverted.\n";
        return false;
    }
}
