<?php


namespace modules\Memberships\Fields;


use craft\base\Field;
use craft\elements\db\ElementQueryInterface;
use craft\fields\MultiSelect;

class Permission extends MultiSelect
{
    public static function displayName(): string
    {
        return \Craft::t('app', 'Permissions');
    }

}