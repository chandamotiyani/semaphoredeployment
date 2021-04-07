<?php

namespace craft\contentmigrations;

use Craft;
use craft\db\Migration;
use craft\db\mysql\Schema;

/**
 * m200510_005532_APIHistory migration.
 */
class m200510_005532_APIHistory extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        // Place migration code here...
        $this->dropTableIfExists('{{%yalumba_api_log}}');
        $this->createTable('{{%yalumba_api_log}}', [
            'id'=>Schema::TYPE_PK,
            'name'=>Schema::TYPE_STRING,
            'dateUpdated'=>Schema::TYPE_DATETIME,
            'dateCreated'=>Schema::TYPE_DATETIME,
            'uid'=>Schema::TYPE_STRING,
            'dateDeleted'=>Schema::TYPE_DATETIME,
            'data'=>Schema::TYPE_TEXT,
            'elementId'=>Schema::TYPE_INTEGER
        ]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m200510_005532_APIHistory cannot be reverted.\n";
        return false;
    }
}
