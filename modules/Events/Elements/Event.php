<?php


namespace modules\Events\Elements;


use Craft;
use craft\base\Element;
use craft\commerce\base\Purchasable;
use craft\elements\actions\Delete;
use craft\elements\actions\Edit;
use craft\elements\actions\Restore;
use craft\elements\actions\SetStatus;
use craft\elements\db\ElementQueryInterface;
use craft\helpers\DateTimeHelper;
use craft\helpers\UrlHelper;
use modules\Events\Elements\Db\EventQuery;
use modules\Events\Elements\Traits\Routing;
use modules\Events\Elements\Traits\Statuses;
use modules\Events\EventsModule;
use modules\Events\Models\EventGroupModel;
use modules\Events\Models\Records\EventRecord;
use modules\Events\Models\Records\ScheduleRecord;
use yii\base\InvalidConfigException;
use yii\db\ActiveQueryInterface;
use yii\db\Query;
use yii\helpers\ArrayHelper;

class Event extends Element {

	use Routing;

	public $price = 0;
	public $sku = 0;
	public $id;
	public $groupId;
	public $groupHandle;
	public $apiType;
	public $apiId;


	/**
	 * @inheritdoc
	 */
	public static function find(): ElementQueryInterface {
		return (new EventQuery( static::class ));
	}

	/**
	 * @inheritdoc
	 */
	protected static function defineSources( string $context = null ): array {

		$sources = [];

		$sources[] = [
			'key'      => '*',
			'label'    => 'All Events',
			'criteria' => [],
		];
		foreach ( EventsModule::getInstance()->events->getAllEventGroups() as $eventGroup ) {
			$sources[] = [
				'key'      => 'eventgroup:' . $eventGroup->uid,
				'label'    => Craft::t( 'site', $eventGroup->name ),
				'criteria' => [ 'groupId' => $eventGroup->id ],
				'data'     => [
					'handle' => $eventGroup->handle,
				],
			];
		}

		return $sources;
	}

	/**
	 * @inheritdoc
	 */
	protected static function defineTableAttributes(): array {
		$attributes = [
			'title' => [ 'label' => "Name" ],
            'dateCreated'=>['label'=>'Date Created'],
			'link'      => [ 'label' => Craft::t( 'commerce', 'Link' ), 'icon' => 'world' ]
		];

		return $attributes;
	}

	protected static function defineActions(string $source = null): array
	{
		$actions = [];
		$elementsService = Craft::$app->getElements();
		$actions[] = $elementsService->createAction([
			'type' => Delete::class,
			'confirmationMessage' => Craft::t('app', 'Are you sure you want to delete the selected events?'),
			'successMessage' => Craft::t('app', 'Events deleted.'),
		]);
		$actions[] = $elementsService->createAction([
			'type' => Restore::class
		]);
        $actions[] = $elementsService->createAction([
            'type' => SetStatus::class
        ]);
		$actions[] =  $elementsService->createAction([
			'type' => Edit::class
		]);
		return $actions;
		
	}

    protected static function defineSortOptions(): array
    {
        return [
            'title' => 'Title',
            'price' => 'Price',
            'status'=>'Status',
            'dateCreated'=>'dateCreated'
        ];
    }


	/**
	 *
	 * Returns whether the current user can edit the element.
	 *
	 * @return bool
	 */
	public function getIsEditable(): bool {
		return true;
	}

	/**
	 * @inheritdoc
	 */
	public static function displayName(): string {
		return 'Event';
	}

	/**
	 * @inheritdoc
	 */
	public static function pluralDisplayName(): string {
		return 'Events';
	}

	/**
	 * @inheritdoc
	 */
	public static function refHandle() {
		return 'event';
	}

    public static function hasStatuses(): bool
    {
        return true;
    }


    /**
	 * @inheritdoc
	 */
	public static function hasContent(): bool {
		return true;
	}

	/**
	 * @inheritdoc
	 */
	public static function hasTitles(): bool {
		return true;
	}

	/**
	 * @inheritdoc
	 */
	protected function tableAttributeHtml( string $attribute ): string {
		switch ( $attribute ) {
			default:
			{
				return parent::tableAttributeHtml( $attribute );
			}
		}
	}

	/**
	 * Returns the field layout used by this element.
	 *
	 * @return \craft\models\FieldLayout
	 * @throws InvalidConfigException
	 */
	public function getFieldLayout() {
		return parent::getFieldLayout() ?? $this->getGroup()->getFieldLayout();
	}

	/**
	 * Returns the event's group.
	 *
	 * @return EventGroupModel
	 * @throws InvalidConfigException if [[groupId]] is missing or invalid
	 */
	public function getGroup(): EventGroupModel {
		if ( $this->groupId === null ) {
			throw new InvalidConfigException( 'Event is missing its group ID' );
		}

		if ( ( $group = EventsModule::getInstance()->events->getEventGroupById( $this->groupId ) ) === null ) {
			throw new InvalidConfigException( 'Invalid event group ID: ' . $this->groupId );
		}

		return $group;
	}

	/**
	 * @inheritdoc
	 */
	public function getCpEditUrl() {
		return UrlHelper::cpUrl( 'events/' . $this->id . "/edit" );
	}

	public function getEditorHtml(): string {

		$html = \Craft::$app->getView()->renderTemplateMacro( '_includes/forms', 'textField', [
			[
				'label'     => \Craft::t( 'app', 'Title' ),
				'siteId'    => $this->siteId,
				'id'        => 'title',
				'name'      => 'title',
				'value'     => $this->title,
				'errors'    => $this->getErrors( 'title' ),
				'first'     => true,
				'autofocus' => true,
				'required'  => true,
			],
		] );

		$html .= parent::getEditorHtml();

		return $html;
	}


	/**
	 * @inheritdoc
	 */
	public function afterSave( bool $isNew ) {
		if ( $isNew ) {
			$record     = new EventRecord();
			$record->id = (int) $this->id;
		}
		else{
			$record = EventRecord::findOne($this->id);

			if (!$record) {
				throw new Exception('Invalid event ID: ' . $this->id);
			}
		}
		$record->groupId = $this->groupId;
		$record->groupHandle = $this->groupHandle;
		$record->price = $this->price;
		$record->sku = $this->sku;
		$record->apiType = $this->apiType;
		$record->apiId = $this->apiId;
		$record->save();
		parent::afterSave( $isNew );
	}


	/**
	 * @inheritdoc
	 */
	public function beforeDelete(): bool
	{
		if (!parent::beforeDelete()) {
			return false;
		}
        foreach($this->schedule->all() as $schedule){
            Craft::$app->elements->deleteElement($schedule);
        }
		return true;
	}

    public function getPrice(): float {
        return $this->price;
    }
}