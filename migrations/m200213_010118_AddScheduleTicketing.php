<?php

namespace craft\contentmigrations;

use Craft;
use craft\db\Migration;
use craft\db\mysql\Schema;
use craft\helpers\StringHelper;
use yii\db\Expression;

/**
 * m200213_010118_AddScheduleTicketing migration.
 */
class m200213_010118_AddScheduleTicketing extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
	    $this->addColumn('{{%eventschedule}}','ticketsAvailable', Schema::TYPE_STRING);
	    $this->addColumn('{{%eventschedule}}','tickets', Schema::TYPE_STRING);
	    $this->addColumn('{{%eventschedule}}','groupId', Schema::TYPE_INTEGER);
	    $this->addColumn('{{%eventschedule}}','price', Schema::TYPE_FLOAT);

	    $this->dropTableIfExists('{{%eventschedulegroup}}');
	    $this->createTable('{{%eventschedulegroup}}', [
		    'id'=>Schema::TYPE_PK,
		    'name'=>Schema::TYPE_STRING,
		    'dateUpdated'=>Schema::TYPE_DATETIME,
		    'dateCreated'=>Schema::TYPE_DATETIME,
		    'uid'=>Schema::TYPE_STRING,
		    'dateDeleted'=>Schema::TYPE_DATETIME,
		    'fieldLayoutId'=>Schema::TYPE_INTEGER,
		    'handle'=>Schema::TYPE_STRING
        ]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m200213_010118_AddScheduleTicketing cannot be reverted.\n";
        return false;
    }
}
