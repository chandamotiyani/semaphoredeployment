<?php

namespace craft\contentmigrations;

use Craft;
use craft\db\Migration;
use modules\Events\Elements\Schedule;

/**
 * m200306_000925_EnterMinimumNotice migration.
 */
class m200306_000925_EnterMinimumNotice extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $schedules = Schedule::find()->all();
        foreach($schedules as $schedule){
            $schedule->noticeDateTime = $schedule->startDateTime;
            Craft::$app->elements->saveElement($schedule);
        }
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m200306_000925_EnterMinimumNotice cannot be reverted.\n";
        return false;
    }
}
