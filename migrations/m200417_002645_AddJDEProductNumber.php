<?php

namespace craft\contentmigrations;

use Craft;
use craft\db\Migration;
use craft\db\mysql\Schema;

/**
 * m200417_002645_AddJDEProductNumber migration.
 */
class m200417_002645_AddJDEProductNumber extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn('{{%yalumba_products}}','jdeProductNumber', Schema::TYPE_STRING);
//        $field-
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m200417_002645_AddJDEProductNumber cannot be reverted.\n";
        return false;
    }
}
