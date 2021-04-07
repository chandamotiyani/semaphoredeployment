<?php

namespace craft\contentmigrations;

use Craft;
use craft\db\Migration;
use craft\db\mysql\Schema;

/**
 * m200422_080919_AddMinimumNoticeField migration.
 */
class m200422_080919_AddMinimumNoticeField extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn('{{%yalumba_api_events}}','minimumNotice', Schema::TYPE_STRING);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m200422_080919_AddMinimumNoticeField cannot be reverted.\n";
        return false;
    }
}
