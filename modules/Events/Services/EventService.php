<?php


namespace modules\Events\Services;


use craft\db\Table;
use craft\events\ConfigEvent;
use modules\Events\Events\EventGroupEvent;
use craft\helpers\ArrayHelper;
use craft\helpers\Db;
use craft\helpers\ProjectConfig;
use craft\models\FieldLayout;
use modules\Events\Elements\Event;
use modules\Events\Elements\Schedule;
use modules\Events\Models\EventGroupModel;

use modules\Events\Models\Records\EventGroupRecord as EventGroupRecord;
use yii\base\Component;
use craft\helpers\StringHelper;
use \Craft;

class EventService extends Component {

	const CONFIG_EVENTGROUP_KEY = 'eventGroups';
	const EVENT_BEFORE_SAVE_GROUP = 'beforeSaveGroup';
	const EVENT_AFTER_SAVE_GROUP = 'afterSaveGroup';
	const EVENT_BEFORE_DELETE_GROUP = 'beforeDeleteGroup';

	const EVENT_BEFORE_APPLY_GROUP_DELETE = 'beforeApplyGroupDelete';
	const EVENT_AFTER_DELETE_GROUP = 'afterDeleteGroup';

	private $_eventGroups;
	private $_events;

	public function getEventGroupById(int $groupId)
	{
		return ArrayHelper::firstWhere($this->getAllEventGroups(), 'id', $groupId);
	}

	public function getEventGroupByHandle(string $groupHandle)
	{
		return ArrayHelper::firstWhere($this->getAllEventGroups(), 'handle', $groupHandle);
	}

	public function getEventByHandle(string $eventHandle)
	{
		return ArrayHelper::firstWhere($this->getAllEvents(), 'handle', $eventHandle);
	}

	public function getAllEvents() {
		if ($this->_events === null) {
			$this->_events = Event::find()
                ->withPermission()
                ->orderBy(['title' => SORT_ASC])
                ->all();
		}
		return $this->_events;
	}



	/**
	 * Returns all event groups.
	 *
	 * @return EventGroupModel[]
	 */
	public function getAllEventGroups(): array
	{
		if ($this->_eventGroups !== null) {
			return $this->_eventGroups;
		}

		$this->_eventGroups = [];
		$records = EventGroupRecord::find()
								->with('events')
		                         ->orderBy(['name' => SORT_ASC])
		                         ->all();

		foreach ($records as $record) {
			$model = new EventGroupModel($record);
			$model->setEvents($record->events);
			$this->_eventGroups[] = $model;
			//$this->_eventGroups[] = new EventGroupModel($model);
		}
		return $this->_eventGroups;
	}

	public function deleteEventGroupById(int $groupId): bool
	{
		if (!$groupId) {
			return false;
		}

		$group = $this->getEventGroupById($groupId);

		if (!$group) {
			return false;
		}

		return $this->deleteEventGroup($group);
	}

	public function deleteEventGroup(EventGroupModel $eventGroup): bool
	{
		if (!$eventGroup) {
			return false;
		}

		// Fire a 'beforeDeleteGroup' event
		if ($this->hasEventHandlers(self::EVENT_BEFORE_DELETE_GROUP)) {
			$this->trigger(self::EVENT_BEFORE_DELETE_GROUP, new EventGroupEvent([
				'eventGroup' => $eventGroup
			]));
		}

		Craft::$app->getProjectConfig()->remove(self::CONFIG_EVENTGROUP_KEY . '.' . $eventGroup->uid);
		return true;
	}

	public function saveEventGroup(EventGroupModel $eventGroup, bool $runValidation = false): bool
	{

		$isNewEventGroup = !$eventGroup->id;

		// Fire a 'beforeSaveGroup' event
		if ($this->hasEventHandlers(self::EVENT_BEFORE_SAVE_GROUP)) {
			$this->trigger(self::EVENT_BEFORE_SAVE_GROUP, new EventGroupEvent([
				'eventGroup' => $eventGroup,
				'isNew' => $isNewEventGroup
			]));
		}

		if ($runValidation && !$eventGroup->validate()) {
			Craft::info('Event group not saved due to validation error.', __METHOD__);
			return false;
		}

		if ($isNewEventGroup) {
			$eventGroup->uid = StringHelper::UUID();
		} else if (!$eventGroup->uid) {
			$eventGroup->uid = Db::uidById('{{%yalumba_eventgroups}}', $eventGroup->id);
		}

		$projectConfig = \Craft::$app->getProjectConfig();
		$configData = [
			'name' => $eventGroup->name,
			'handle' => $eventGroup->handle,
		];

		$fieldLayout = $eventGroup->getFieldLayout();
		$fieldLayoutConfig = $fieldLayout->getConfig();

		if ($fieldLayoutConfig) {
			if (empty($fieldLayout->id)) {
				$layoutUid = StringHelper::UUID();
				$fieldLayout->uid = $layoutUid;
			} else {
				$layoutUid = Db::uidById('{{%yalumba_eventgroups}}', $fieldLayout->id);
			}

			$configData['fieldLayouts'] = [
				$layoutUid => $fieldLayoutConfig
			];
		}

		$configPath = self::CONFIG_EVENTGROUP_KEY . '.' . $eventGroup->uid;
		$projectConfig->set($configPath, $configData);

		if ($isNewEventGroup) {
			$eventGroup->id = Db::idByUid('{{%yalumba_eventgroups}}', $eventGroup->uid);
		}

		return true;
	}


	public function handleDeletedEventGroup(ConfigEvent $event){
		$uid = $event->tokenMatches[0];
		$eventGroupRecord = $this->_getEventGroupRecord($uid);

		if (!$eventGroupRecord->id) {
			return;
		}

		/** @var EventGroupModel $eventGroup */
		$eventGroup = $this->getEventGroupById($eventGroupRecord->id);

		// Fire a 'beforeApplyGroupDelete' event
		if ($this->hasEventHandlers(self::EVENT_BEFORE_APPLY_GROUP_DELETE)) {
			$this->trigger(self::EVENT_BEFORE_APPLY_GROUP_DELETE, new EventGroupEvent([
				'eventGroup' => $eventGroup,
			]));
		}

		$transaction = Craft::$app->getDb()->beginTransaction();
		try {
			// Delete the tags
			$events = Event::find()
			               ->anyStatus()
			               ->groupId($eventGroupRecord->id)
			               ->all();
			$elementsService = Craft::$app->getElements();

			foreach ($events as $event) {
				$event->deletedWithGroup = true;
				$elementsService->deleteElement($event);
			}

			// Delete the field layout
			if ($eventGroupRecord->fieldLayoutId) {
				Craft::$app->getFields()->deleteLayoutById($eventGroupRecord->fieldLayoutId);
			}

			// Delete the event group
			Craft::$app->getDb()->createCommand()
			           ->softDelete('{{%yalumba_eventgroups}}', ['id' => $eventGroupRecord->id])
			           ->execute();

			$transaction->commit();
		} catch (\Throwable $e) {
			$transaction->rollBack();
			throw $e;
		}

		// Clear caches
		$this->_eventGroups = null;

		// Fire an 'afterDeleteGroup' event
		if ($this->hasEventHandlers(self::EVENT_AFTER_DELETE_GROUP)) {
			$this->trigger(self::EVENT_AFTER_DELETE_GROUP, new EventGroupEvent([
				'eventGroup' => $eventGroup
			]));
		}
	}

	/**
	 * Handle event group change
	 * @param ConfigEvent $event
	 *
	 * @throws \Throwable
	 */
	public function handleChangedEventGroup(ConfigEvent $event)
	{
		$eventGroupUid = $event->tokenMatches[0];
		$data = $event->newValue;

		// Make sure fields are processed
		ProjectConfig::ensureAllFieldsProcessed();

		$transaction = Craft::$app->getDb()->beginTransaction();
		try {
			$eventGroupRecord = $this->_getEventGroupRecord($eventGroupUid, true);
			$isNewEventGroup = $eventGroupRecord->getIsNewRecord();

			$eventGroupRecord->name = $data['name'];
			$eventGroupRecord->handle = $data['handle'];
			$eventGroupRecord->uid = $eventGroupUid;

			if (!empty($data['fieldLayouts'])) {
				// Save the field layout
				$layout = FieldLayout::createFromConfig(reset($data['fieldLayouts']));
				$layout->id = $eventGroupRecord->fieldLayoutId;
				$layout->type = Event::class;
				$layout->uid = key($data['fieldLayouts']);
				Craft::$app->getFields()->saveLayout($layout);
				$eventGroupRecord->fieldLayoutId = $layout->id;
			} else if ($eventGroupRecord->fieldLayoutId) {
				// Delete the field layout
				Craft::$app->getFields()->deleteLayoutById($eventGroupRecord->fieldLayoutId);
				$eventGroupRecord->fieldLayoutId = null;
			}

			// Save the tag group
			if ($wasTrashed = (bool)$eventGroupRecord->dateDeleted) {
				$eventGroupRecord->restore();
			} else {
				$eventGroupRecord->save(false);
			}

			$transaction->commit();
		} catch (\Throwable $e) {
			$transaction->rollBack();
			throw $e;
		}

		// Clear caches
		$this->_eventGroups = null;

		if ($wasTrashed) {
			// Restore the tags that were deleted with the group
			$events = Event::find()
			               ->groupId($eventGroupRecord->id)
			               ->trashed()
			               ->andWhere(['events.deletedWithGroup' => true])
			               ->all();
			Craft::$app->getElements()->restoreElements($events);
		}

		// Fire an 'afterSaveGroup' event
		if ($this->hasEventHandlers(self::EVENT_AFTER_SAVE_GROUP)) {
			$this->trigger(self::EVENT_AFTER_SAVE_GROUP, new EventGroupEvent([
				'eventGroup' => $this->getEventGroupById($eventGroupRecord->id),
				'isNew' => $isNewEventGroup,
			]));
		}
	}

	/**
	 * Gets a tag group's record by uid.
	 *
	 * @param string $uid
	 * @param bool $withTrashed Whether to include trashed tag groups in search
	 * @return TagGroupRecord
	 */
	private function _getEventGroupRecord(string $uid, bool $withTrashed = false): EventGroupRecord
	{
		$query = $withTrashed ? EventGroupRecord::findWithTrashed() : EventGroupRecord::find();
		$query->andWhere(['uid' => $uid]);
		return $query->one() ?? new EventGroupRecord();
	}
}