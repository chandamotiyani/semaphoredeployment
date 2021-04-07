<?php

namespace craft\contentmigrations;

use Craft;
use craft\db\Migration;

/**
 * m200225_005035_MoveEventTables migration.
 */
class m200225_005035_MoveEventTables extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
    	$this->renameTable('{{%eventschedule}}', '{{%yalumba_eventschedule}}');
	    $this->renameTable('{{%events}}', '{{%yalumba_events}}');
	    $this->renameTable('{{%eventgroups}}', '{{%yalumba_eventgroups}}');
	    $this->renameTable('{{%eventschedulegroup}}', '{{%yalumba_eventschedulegroup}}');

        // Place migration code here...
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m200225_005035_MoveEventTables cannot be reverted.\n";
        return false;
    }
}
