<?php

namespace craft\contentmigrations;

use Craft;
use craft\db\Migration;
use yii\db\Schema;

/**
 * m191030_053420_EventsSetup migration.
 */
class m191030_053420_EventsSetup extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
	    $this->dropTableIfExists('{{%events}}'); //there's some stuff here already - we'll just strip it out for now..
	    $this->dropTableIfExists('{{%eventgroups}}'); //there's some stuff here already - we'll just strip it out for now..
	    $this->createTable('{{%eventgroups}}', [
		    'id' => Schema::TYPE_PK,
		    'name' => Schema::TYPE_STRING . ' NOT NULL',
		    'handle' => Schema::TYPE_STRING . ' NOT NULL',
		    'fieldLayoutId'=>Schema::TYPE_INTEGER,
		    'dateCreated'=>Schema::TYPE_DATETIME,
		    'dateUpdated'=>Schema::TYPE_DATETIME,
		    'dateDeleted'=>Schema::TYPE_DATETIME,
		    'uid'=> Schema::TYPE_STRING,
	    ]);

	    $this->createTable('{{%events}}', [
		    'id' => Schema::TYPE_PK,
		    'sku' => Schema::TYPE_STRING . ' NOT NULL',
		    'price' => Schema::TYPE_DECIMAL,
		    'dateCreated'=>Schema::TYPE_DATETIME,
		    'dateUpdated'=>Schema::TYPE_DATETIME,
		    'uid'=> Schema::TYPE_STRING,
		    'groupId'=>Schema::TYPE_INTEGER,
		    'eventType'=>Schema::TYPE_STRING,
		    'apiType'=>Schema::TYPE_STRING,
		    'apiId'=>Schema::TYPE_STRING,
		    'startDateTime'=>Schema::TYPE_DATETIME,
		    'endDateTime'=>Schema::TYPE_DATETIME,
	    ]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
	    $this->dropTableIfExists('{{%eventgroups}}');
	    $this->dropTableIfExists('{{%events}}');
    }
}
