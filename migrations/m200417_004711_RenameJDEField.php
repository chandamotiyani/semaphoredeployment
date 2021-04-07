<?php

namespace craft\contentmigrations;

use Craft;
use craft\db\Migration;

/**
 * m200417_004711_RenameJDEField migration.
 */
class m200417_004711_RenameJDEField extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $field = Craft::$app->fields->getFieldByHandle('gdeProductNumber');
        $field->handle = 'jdeProductNumber';
        $field->name = 'JDE Product Number';
        Craft::$app->fields->saveField($field);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m200417_004711_RenameJDEField cannot be reverted.\n";
        return false;
    }
}
