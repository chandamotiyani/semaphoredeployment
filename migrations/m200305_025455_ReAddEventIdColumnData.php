<?php

namespace craft\contentmigrations;

use Craft;
use craft\db\Migration;
use modules\Events\Elements\Event;

/**
 * m200305_025455_ReAddEventIdColumnData migration.
 */
class m200305_025455_ReAddEventIdColumnData extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $events = Event::find()->all();

        foreach($events as $event){
            foreach($event->schedule->all() as $schedule){
                $schedule->eventId = $event->id;
                Craft::$app->elements->saveElement($schedule);
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m200305_025455_ReAddEventIdColumnData cannot be reverted.\n";
        return false;
    }
}
