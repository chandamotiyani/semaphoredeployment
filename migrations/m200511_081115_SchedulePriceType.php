<?php

namespace craft\contentmigrations;

use Craft;
use craft\db\Migration;
use craft\db\mysql\Schema;

/**
 * m200511_081115_SchedulePriceType migration.
 */
class m200511_081115_SchedulePriceType extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->alterColumn('{{%yalumba_events}}', 'price', $this->decimal(14, 4)->notNull()->defaultValue(0));
        // Place migration code here...
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m200511_081115_SchedulePriceType cannot be reverted.\n";
        return false;
    }
}
