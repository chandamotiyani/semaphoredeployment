<?php

namespace craft\contentmigrations;

use Craft;
use craft\db\Migration;
use craft\db\mysql\Schema;
use modules\Events\Elements\Schedule;

/**
 * m200305_055341_AddScheduleNotificationDate migration.
 */
class m200305_055341_AddScheduleNotificationDate extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn('{{%yalumba_eventschedule}}','noticeDateTime', Schema::TYPE_DATETIME);


        $this->addColumn('{{%yalumba_api_events}}','minimumNotice', Schema::TYPE_INTEGER);
        // Place migration code here...
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m200305_055341_AddScheduleNotificationDate cannot be reverted.\n";
        return false;
    }
}
