<?php
/**
 * @link https://craftcms.com/
 * @copyright Copyright (c) Pixel & Tonic, Inc.
 * @license https://craftcms.github.io/license/
 */

namespace modules\Events\Models;

use Craft;
use craft\base\Model;
use craft\behaviors\FieldLayoutBehavior;
//use craft\records\TagGroup as TagGroupRecord;
use modules\Events\Elements\Schedule;
use modules\Events\Models\Records\EventGroupRecord as EventGroupRecord;
use craft\validators\HandleValidator;
use craft\validators\UniqueValidator;
use modules\Events\Elements\Event;
use yii\db\ActiveQueryInterface;

/**
 * EventGroup model.
 *
 * @mixin FieldLayoutBehavior
 * @author Pixel & Tonic, Inc. <support@pixelandtonic.com>
 * @since 3.0
 */
class ScheduleGroupModel extends Model
{
    // Properties
    // =========================================================================

    /**
     * @var int|null ID
     */
    public $id;

    /**
     * @var string|null Name
     */
    public $name;

    /**
     * @var int|null Field layout ID
     */
    public $fieldLayoutId;

    /**
     * @var int|null Field layout ID
     */
    public $uid;

private $schedule;

	public $dateCreated;
	public $dateUpdated;
	public $dateDeleted;

	public $handle;

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'fieldLayout' => [
                'class' => FieldLayoutBehavior::class,
                'elementType' => Schedule::class
            ]
        ];
    }



    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'handle' => Craft::t('app', 'Handle'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = parent::rules();
        $rules[] = [['id', 'fieldLayoutId'], 'number', 'integerOnly' => true];
        $rules[] = [['handle'], HandleValidator::class, 'reservedWords' => ['id', 'dateCreated', 'dateUpdated', 'uid', 'title']];
        $rules[] = [['handle'], UniqueValidator::class, 'targetClass' => EventGroupRecord::class];
        $rules[] = [['handle'], 'required'];
        $rules[] = [['handle'], 'string', 'max' => 255];
        return $rules;
    }

    /**
     * Use the translated event group's name as the string representation.
     *
     * @return string
     */
    public function __toString(): string
    {
        return Craft::t('site', $this->name) ?: static::class;
    }

}
