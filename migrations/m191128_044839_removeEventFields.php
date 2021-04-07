<?php

namespace craft\contentmigrations;

use Craft;
use craft\db\Migration;

/**
 * m191128_044839_removeEventFields migration.
 */
class m191128_044839_removeEventFields extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
	    $this->dropColumn('{{%events}}', 'startDateTime');
	    $this->dropColumn('{{%events}}', 'endDateTime');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m191128_044839_removeEventFields cannot be reverted.\n";
        return false;
    }
}
