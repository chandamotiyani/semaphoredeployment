<?php

namespace craft\contentmigrations;

use Craft;
use craft\db\Migration;
use yii\db\Schema;

/**
 * m191106_062807_CreateSessions migration.
 */
class m191106_062807_CreateSchedule extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
	    $this->dropTableIfExists('{{%eventschedule}}');
	    $this->createTable('{{%eventschedule}}', [
		    'id' => Schema::TYPE_PK,
		    'dateCreated'=>Schema::TYPE_DATETIME,
		    'dateUpdated'=>Schema::TYPE_DATETIME,
		    'dateDeleted'=>Schema::TYPE_DATETIME,
		    'startDateTime'=>Schema::TYPE_DATETIME,
		    'endDateTime'=>Schema::TYPE_DATETIME,
		    'allDay'=>Schema::TYPE_BOOLEAN,
		    'apiId'=>Schema::TYPE_STRING,
		    'eventId'=>Schema::TYPE_INTEGER,
		    'uid'=> Schema::TYPE_STRING,
	    ]);
        // Place migration code here...
    }

    /**
     * @inheritdoc
     */
    public function safeDown(){
	    $this->dropTableIfExists('{{%eventschedule}}');
    }
}
