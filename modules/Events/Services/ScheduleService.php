<?php


namespace modules\Events\Services;


use craft\base\Component;
use craft\db\Query;
use craft\elements\Category;
use craft\events\ConfigEvent;
use craft\helpers\ArrayHelper;
use craft\helpers\DateTimeHelper;
use craft\helpers\Db;
use craft\helpers\ProjectConfig;
use craft\models\FieldLayout;
use modules\Events\Elements\Event;
use modules\Events\Elements\Schedule;
use modules\Events\Events\ScheduleGroupEvent;
use modules\Events\EventsModule;
use modules\Events\Models\Records\ScheduleGroupRecord;
use modules\Events\Models\ScheduleGroupModel;
use \modules\Events\Models\ScheduleModel;
use \modules\Events\Models\Records\ScheduleRecord as ScheduleRecord;
use \Craft;
use yii\db\Expression;
use craft\helpers\StringHelper;

/**
 *
 * @property void $scheduleById
 * @property null|array $allSchedules
 */
class ScheduleService extends Component {

	const CONFIG_SCHEDULEGROUP_KEY = 'scheduleGroups';
	const EVENT_BEFORE_SAVE_GROUP = 'beforeSaveGroup';
	const EVENT_AFTER_SAVE_GROUP = 'afterSaveGroup';
	const EVENT_BEFORE_DELETE_GROUP = 'beforeDeleteGroup';

	private $_schedules = null;
	private $_scheduleGroups = null;

	public function getAllScheduleGroups(): array
	{
		if ($this->_scheduleGroups !== null) {
			return $this->_scheduleGroups;
		}
		$records = ScheduleGroupRecord::find()
		                           ->all();
		$this->_scheduleGroups = [];
		foreach ($records as $record) {
			$model = new ScheduleGroupModel($record);
			$this->_scheduleGroups[] = $model;
		}
		return $this->_scheduleGroups;
	}


	public function getScheduleGroupById($groupId)
	{
		return ArrayHelper::firstWhere($this->getAllScheduleGroups(), 'id', $groupId);
	}

	public function getScheduleGroupByHandle(string $handle)
	{
		return ArrayHelper::firstWhere($this->getAllScheduleGroups(), 'handle', $handle);
	}



	/**
	 * Gets a tag group's record by uid.
	 *
	 * @param string $uid
	 * @param bool $withTrashed Whether to include trashed tag groups in search
	 * @return ScheduleGroupRecord
	 */
	private function _getScheduleGroupRecord(string $uid, bool $withTrashed = false): ScheduleGroupRecord
	{
		$query = $withTrashed ? ScheduleGroupRecord::findWithTrashed() : ScheduleGroupRecord::find();
		$query->andWhere(['uid' => $uid]);
		return $query->one() ?? new ScheduleGroupRecord();
	}

	/**
	 * get all schedules for an event group
	 * @param int $eventGroupId
	 *
	 * @return array|null
	 */
	public function getEventGroupSchedule(int $eventGroupId){
		$eventSchedule = [];
		$ev = EventsModule::getInstance()->events;
		$eventGroup = $ev->getEventGroupById($eventGroupId);
		if($eventGroup){
			$events = Event::find()->where([ 'groupId' =>$eventGroup->id])->all();
			$eventIds = ArrayHelper::getColumn($events, 'id');
			$eventSchedule = $this->getEventSchedule($eventIds);
		}
		return $eventSchedule;
	}

	private function addSchedule($item, $schedule, Event $event){
	  $start =  DateTimeHelper::toDateTime($item['startDateTime']);
    $notice =  DateTimeHelper::toDateTime($item['startDateTime']);
    if(isset($event->minimumNotice) && $event->minimumNotice){
      $notice->sub(new \DateInterval("PT{$event->minimumNotice}H"));
  	}
        //TODO ^^ DRY this up
		$schedule->startDateTime = $start;
		$schedule->endDateTime = isset($item['endDateTime'])?DateTimeHelper::toDateTime($item['endDateTime']):NULL;
		$schedule->ticketsAvailable = isset($item['ticketsAvailable'])?$item['ticketsAvailable']:NULL;
		$schedule->tickets = isset($item['tickets'])?$item['tickets']:NULL;
		$schedule->groupId = isset($item['groupId'])?$item['groupId']:NULL;
		$schedule->eventId = $event->id;
		$schedule->noticeDateTime = $notice;
		$schedule->eventId = $event->id;
		Craft::$app->elements->saveElement($schedule, false, true, false);
		return $schedule;
	}


	public function saveScheduleGroup(ScheduleGroupModel $scheduleGroup, bool $runValidation = false): bool
	{
		$isNewScheduleGroup = !$scheduleGroup->id;

		// Fire a 'beforeSaveGroup' event
		if ($this->hasEventHandlers(self::EVENT_BEFORE_SAVE_GROUP)) {
			$this->trigger(self::EVENT_BEFORE_SAVE_GROUP, new ScheduleGroupEvent([
				'scheduleGroup' => $scheduleGroup,
				'isNew' => $isNewScheduleGroup
			]));
		}


		if ($runValidation && !$scheduleGroup->validate()) {
			Craft::info('Schedule group not saved due to validation error.', __METHOD__);
			return false;
		}

		if ($isNewScheduleGroup) {
			$scheduleGroup->uid = StringHelper::UUID();
		} else if (!$scheduleGroup->uid) {
			$scheduleGroup->uid = Db::uidById('{{%yalumba_eventschedulegroup}}', $scheduleGroup->id);
		}

		$projectConfig = \Craft::$app->getProjectConfig();
		$configData = [
			'name' => $scheduleGroup->name,
			'handle' => $scheduleGroup->handle,
		];

		$fieldLayout = $scheduleGroup->getFieldLayout();
		$fieldLayoutConfig = $fieldLayout->getConfig();

		if ($fieldLayoutConfig) {
			if (empty($fieldLayout->id)) {
				$layoutUid = StringHelper::UUID();
				$fieldLayout->uid = $layoutUid;
			} else {
				$layoutUid = Db::uidById('{{%yalumba_eventschedulegroup}}', $fieldLayout->id);
			}

			$configData['fieldLayouts'] = [
				$layoutUid => $fieldLayoutConfig
			];
		}

		$configPath = self::CONFIG_SCHEDULEGROUP_KEY . '.' . $scheduleGroup->uid;
		$projectConfig->set($configPath, $configData);

		if ($isNewScheduleGroup) {
			$scheduleGroup->id = Db::idByUid('{{%yalumba_eventschedulegroup}}', $scheduleGroup->uid);
		}

		return true;
	}

	/**
	 * Handle event group change
	 * @param ConfigEvent $event
	 *
	 * @throws \Throwable
	 */
	public function handleChangedScheduleGroup(ConfigEvent $event)
	{
		$scheduleGroupUid = $event->tokenMatches[0];
		$data = $event->newValue;

		// Make sure fields are processed
		ProjectConfig::ensureAllFieldsProcessed();

		$transaction = Craft::$app->getDb()->beginTransaction();
		try {
			$scheduleGroupRecord = $this->_getScheduleGroupRecord($scheduleGroupUid, true);
			$isNewScheduleGroup = $scheduleGroupRecord->getIsNewRecord();

			$scheduleGroupRecord->name = $data['name'];
			$scheduleGroupRecord->handle = $data['handle'];
			$scheduleGroupRecord->uid = $scheduleGroupUid;

			if (!empty($data['fieldLayouts'])) {
				// Save the field layout
				$layout = FieldLayout::createFromConfig(reset($data['fieldLayouts']));
				$layout->id = $scheduleGroupRecord->fieldLayoutId;
				$layout->type = Schedule::class;
				$layout->uid = key($data['fieldLayouts']);
				Craft::$app->getFields()->saveLayout($layout);
				$scheduleGroupRecord->fieldLayoutId = $layout->id;
			} else if ($scheduleGroupRecord->fieldLayoutId) {
				// Delete the field layout
				Craft::$app->getFields()->deleteLayoutById($scheduleGroupRecord->fieldLayoutId);
				$scheduleGroupRecord->fieldLayoutId = null;
			}

			// Save the tag group
			if ($wasTrashed = (bool)$scheduleGroupRecord->dateDeleted) {
				$scheduleGroupRecord->restore();
			} else {
				$scheduleGroupRecord->save(false);
			}

			$transaction->commit();
		} catch (\Throwable $e) {
			$transaction->rollBack();
			throw $e;
		}

		// Clear caches
		$this->_scheduleGroups = null;

		if ($wasTrashed) {
			// Restore the tags that were deleted with the group
			$schedules = Schedule::find()
			               ->groupId($scheduleGroupRecord->id)
			               ->trashed()
			               ->andWhere(['schedule.deletedWithGroup' => true])
			               ->all();
			Craft::$app->getElements()->restoreElements($schedules);
		}

		// Fire an 'afterSaveGroup' event
		if ($this->hasEventHandlers(self::EVENT_AFTER_SAVE_GROUP)) {
			$this->trigger(self::EVENT_AFTER_SAVE_GROUP, new ScheduleGroupEvent([
				'scheduleGroup' => $this->getScheduleGroupById($scheduleGroupRecord->id),
				'isNew' => $isNewScheduleGroup,
			]));
		}
	}


    /**
     * saves event schedules
     * @param array $toAdd
     * @param array $toUpdate
     * @param Event $event
     *
     * @return array
     */
	public function addAndUpdateSchedules(array $toAdd, array $toUpdate, Event $event){
		$scheduleElements = [];

		foreach($toAdd as $item){
			$schedule = new Schedule();
			$schedule = $this->addSchedule($item, $schedule, $event);
			$scheduleElements[] = $schedule;
		}

		foreach($toUpdate as $id=>$item){
			$schedule = Schedule::findOne(['id'=>$id]);
			$schedule = $this->addSchedule($item, $schedule, $event);
			$scheduleElements[] = $schedule;
		}
		return $scheduleElements;
	}

	public function deleteScheduleByIds(array $scheduleIds){
		foreach($scheduleIds as $scheduleId){
			Craft::$app->elements->deleteElementById($scheduleId);
		}
	}

	public function getMembersOnlySchedules() {
		return $this->getEventsWithFirstSchedule($filters = [], $eventGroup = null, $sort = false, $upcomming = true, $membersOnly = true);
	}

	public function getEventsWithFirstSchedule($filters = [], $eventGroup = null, $sort = false, $upcomming = true, $membersOnly = false) {

		// Get Events:
        // Chanda - selecting the minimum date but of future by putting  [MIN(`yalumba_eventschedule`.`startDateTime`) AS `next_on`] in select query and to reference future a where condition [AND DATE(`yalumba_eventschedule`.`startDateTime`) > DATE(NOW())]
        // Chanda - fixing the order of events which should be by scheduled date
        // Chanda - this was not causing issues, hence restoring to previous state
        $scheduleQuery = Schedule::find()->select('`elements`.`id`, `elements`.`fieldLayoutId`, `elements`.`uid`, `elements`.`enabled`, `elements`.`archived`, `elements`.`dateCreated`, `elements`.`dateUpdated`, `elements_sites`.`slug`, `elements_sites`.`siteId`, `elements_sites`.`uri`, `elements_sites`.`enabled` AS `enabledForSite`, `yalumba_eventschedule`.`endDateTime`, MIN(`yalumba_eventschedule`.`startDateTime`) startDateTime, `yalumba_eventschedule`.`ticketsAvailable`, `yalumba_eventschedule`.`tickets`, `yalumba_eventschedule`.`groupId`, `yalumba_eventschedule`.`price`, `yalumba_eventschedule`.`eventId`, `yalumba_eventschedule`.`noticeDateTime`')->addSelect('COUNT(distinct `yalumba_eventschedule`.`id`) as `scheduleCount`, `yalumba_eventschedule`.`eventId` AS `e_id`, MIN(`yalumba_eventschedule`.`startDateTime`) AS `next_on`')->where('`yalumba_eventschedule`.`eventId` IS NOT NULL AND DATE(`yalumba_eventschedule`.`startDateTime`) > DATE(NOW())')->groupBy('eventId')->orderBy('MIN(`yalumba_eventschedule`.`startDateTime`)');//->having(MIN(`yalumba_eventschedule`.`startDateTime`));
        //$scheduleQuery = Schedule::find()->select('`elements`.`id`, `elements`.`fieldLayoutId`, `elements`.`uid`, `elements`.`enabled`, `elements`.`archived`, `elements`.`dateCreated`, `elements`.`dateUpdated`, `elements_sites`.`slug`, `elements_sites`.`siteId`, `elements_sites`.`uri`, `elements_sites`.`enabled` AS `enabledForSite`, `yalumba_eventschedule`.`endDateTime`, MIN(`yalumba_eventschedule`.`startDateTime`) startDateTime, `yalumba_eventschedule`.`ticketsAvailable`, `yalumba_eventschedule`.`tickets`, `yalumba_eventschedule`.`groupId`, `yalumba_eventschedule`.`price`, `yalumba_eventschedule`.`eventId`, `yalumba_eventschedule`.`noticeDateTime`')->addSelect('COUNT(distinct `yalumba_eventschedule`.`id`) as `scheduleCount`, `yalumba_eventschedule`.`eventId` AS `e_id`, MIN(`yalumba_eventschedule`.`startDateTime`) AS `next_on`')->where('`yalumba_eventschedule`.`eventId` IS NOT NULL')->groupBy('eventId')->orderBy('MIN(`yalumba_eventschedule`.`startDateTime`)');//->having(MIN(`yalumba_eventschedule`.`startDateTime`));
		$eventQuery = Event::find()->withPermission();
		if($membersOnly) {
			$eventQuery->andWhere("content.field_memberPermission NOT LIKE '%guest%'");
		}

		return $this->filterSchedules($filters, $eventGroup, $sort, $upcomming, $scheduleQuery, $eventQuery);
	}

	public function filter($filters = [], $eventGroup = null, $sort = false, $upcomming = true) {

		$scheduleQuery = Schedule::find();
		$eventQuery = Event::find();

		return $this->filterSchedules($filters, $eventGroup, $sort, $upcomming, $scheduleQuery, $eventQuery);
	}

	/**
	 * gets filtered schedule of upcoming events.
	 * @param $filters
	 * @param $eventGroup
	 * @param bool $sort
	 * @param bool $upcomming
	 *
	 * @return \craft\elements\db\ElementQueryInterface
	 */
	public function filterSchedules($filters, $eventGroup, $sort = false, $upcomming = true, $scheduleQuery, $eventQuery, $membersOnly=false) {

		if($eventGroup){
			//we need to restrict it to just the tours or tastings etc.
			$eventQuery = $eventQuery->groupHandle($eventGroup);
		}
        $eventQuery = $eventQuery->withPermission();

		foreach($filters as $filter) {
			if($filter['param']) {
				switch ($filter['handle']){
					case 'eventType':
						$filterValues = explode(',', $filter['param']);
						//find EVENTS that apply that have the category:
						$categoryQuery = Category::find()->where(['IN', 'categories.id', $filterValues]);
						$eventQuery = $eventQuery->relatedTo($categoryQuery);
						break;
					case 'day':
						$dayValues = explode(',', $filter['param']);
						$scheduleQuery->andHaving(['IN', 'DAY(utcStartDateTime)', $dayValues]);

						break;
					case 'month':
						$monthValues = explode(',', $filter['param']);

						$scheduleQuery->andHaving(['IN', 'MONTH(utcStartDateTime)', $monthValues]);

						break;
					case 'year':
						$yearValues = explode(',', $filter['param']);
						$scheduleQuery->andHaving(['IN', 'YEAR(utcStartDateTime)', $yearValues]);
						break;
					case 'eventId':
							$eventIds = explode(',', $filter['param']);
							$eventQuery = $eventQuery->id($eventIds);

							break;
					case 'eventLocation':

						// field: locationState   category: eventLocation
						$values = explode(',', $filter['param']);

						$filters = [];

						foreach($values as $value) {
							$filters[] = "locationState:{$value}";
						}

						$filter = implode(' OR ', $filters);

						$categoryQuery = Category::find()->group('eventLocation')->search($filter);

						$eventQuery = $eventQuery->relatedTo($categoryQuery);

						break;
				}
			}
		}

		$dateTime = (new \DateTime())->setTimezone(new \DateTimeZone('UTC'));
        //$scheduleQuery = $scheduleQuery->addSelect('MIN(`yalumba_eventschedule`.`startDateTime`) AS `next_on`')->where('DATE(`yalumba_eventschedule`.`startDateTime`) > DATE(NOW())');
        $scheduleQuery = $scheduleQuery->andWhere(new Expression('STR_TO_DATE(noticeDateTime, \'%Y-%m-%d %H:%i:%s\') > STR_TO_DATE("'.$dateTime->format('Y-m-d H:i:s').'", \'%Y-%m-%d %H:%i:%s\')'));

				// REMOVED as directed by Chanda to fix bookings issue 2020-09-22
				//$scheduleQuery = $scheduleQuery->andWhere('DATE(`yalumba_eventschedule`.`startDateTime`) > DATE(NOW())');

        $scheduleQuery = $scheduleQuery->relatedTo($eventQuery);

		if($sort) {
			//there are no sort options for this.
		}
		else {
			$scheduleQuery = $scheduleQuery->orderBy('startDateTime');//'`utcStartDateTimeutcStartDateTime` ASC'

		}
        //var_dump($scheduleQuery->getRawSql());
		//exit;
		return $scheduleQuery;
	}


	public function getAllSchedules(): array
	{
		if ($this->_schedules !== null) {
			return $this->_schedules;
		}

		$this->_schedules = [];
		$records = ScheduleRecord::find()
		                         ->orderBy(['startDateTime' => SORT_ASC])
		                         ->all();
		foreach ($records as $record) {
			$this->_schedules[] = new ScheduleModel($record);
		}

		return $this->_schedules;
	}
	/**
	 * Gets filters for filter menus in frontend.
	 */
	public function getFilters() {
		return [
			'eventType' => [
				'name'=> 'Event Type',
				'handle'=>'eventType',
				'model'=>'event',
				'type'=>'category',
				'param'=> Craft::$app->request->getParam('eventType'),
			],
			'eventLocation' =>  [
				'name'=> 'Event Location',
				'handle'=>'eventLocation',
				'model'=>'event',
				'type'=>'category',
				'param'=> Craft::$app->request->getParam('eventLocation'),
				'available'=> [
					'NSW'=>'NSW',
					'QLD'=>'QLD',
					'SA'=>'SA',
					'Tas'=>'Tas',
					'Vic'=>'Vic',
					'WA'=>'WA',
				]
			],
			'month' => [
				'name'=> 'Month',
				'handle'=>'month',
				'model'=>'schedule',
				'type'=>'customField',
				'private'=>'true',
				'param'=> Craft::$app->request->getParam('month'),
				'available'=> [
					'1'=>'January',
					'2'=>'Febuary',
					'3'=>'March',
					'4'=>'April',
					'5'=>'May',
					'6'=>'June',
					'7'=>'July',
					'8'=>'August',
					'9'=>'September',
					'10'=>'October',
					'11'=>'November',
					'12'=>'December',
				]
			],
		];
	}
}
