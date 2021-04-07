<?php

namespace craft\contentmigrations;

use Craft;
use craft\db\Migration;

/**
 * m190820_054309_events migration.
 */
class m190820_054309_events extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        if (!$this->db->tableExists('{{%events}}')) {
            // create the products table
            $this->createTable('{{%events}}', [
                'id' => $this->integer()->notNull(),
                'eventType' => $this->char(100)->notNull(),
                'dateCreated' => $this->dateTime()->notNull(),
                'dateUpdated' => $this->dateTime()->notNull(),
                'uid' => $this->uid(),
                'PRIMARY KEY(id)',
            ]);
        
            // give it a FK to the elements table
            $this->addForeignKey(
                $this->db->getForeignKeyName('{{%products}}', 'id'),
                '{{%products}}', 'id', '{{%elements}}', 'id', 'CASCADE', null);
        }
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m190820_054309_events cannot be reverted.\n";
        return false;
    }
}
