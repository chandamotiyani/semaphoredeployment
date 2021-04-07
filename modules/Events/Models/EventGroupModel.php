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
class EventGroupModel extends Model
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
     * @var string|null Handle
     */
    public $handle;

    /**
     * @var int|null Field layout ID
     */
    public $fieldLayoutId;

    /**
     * @var int|null Field layout ID
     */
    public $uid;

	private $events;

	public $dateCreated;
	public $dateUpdated;
	public $dateDeleted;

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
                'elementType' => Event::class
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
            'name' => Craft::t('app', 'Name'),
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
        $rules[] = [['name', 'handle'], UniqueValidator::class, 'targetClass' => EventGroupRecord::class];
        $rules[] = [['name', 'handle'], 'required'];
        $rules[] = [['name', 'handle'], 'string', 'max' => 255];
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

    public function getEvents(){
    	return $this->events;
    }

	public function setEvents($eventRecords){
    	$events = [];
    	foreach($eventRecords as $eventRecord){
    		$events[] = Craft::$app->getElements()->getElementById($eventRecord->id);
	    }
    	$this->events = $events;
		//return $this->events;
	}
}
