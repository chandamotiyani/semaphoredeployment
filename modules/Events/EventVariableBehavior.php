<?php


namespace modules\Events;


use craft\elements\db\ElementQueryInterface;
use modules\Events\Elements\Schedule;
use modules\Events\Models\Records\EventGroupRecord;
use modules\Events\Models\Records\ScheduleGroupRecord;
use yii\base\Behavior;
use Craft;
use modules\Events\Elements\Event;

class EventVariableBehavior extends Behavior {
	public function getEvents() {
		return EventsModule::getInstance()->events;
	}

	public function events($criteria=null):ElementQueryInterface {
		$query = Event::find()->withPermission();
		if ($criteria) {
			Craft::configure($query, $criteria);
		}
		return $query;
	}

	public function getEventGroups(){
		return EventsModule::getInstance()->events;
	}
	
	public function eventGroups($criteria=null) {
		$query = EventGroupRecord::find();
		if ($criteria) {
			Craft::configure($query, $criteria);
		}
		return $query;
	}

	public function schedules($criteria=null) {
		$query = Schedule::find();
		if ($criteria) {
			Craft::configure($query, $criteria);
		}
		return $query;
	}

	public function getSchedule() {
		return EventsModule::getInstance()->schedule;
	}

	public function scheduleGroups($criteria=null) {
		$query = ScheduleGroupRecord::find();
		if ($criteria) {
			Craft::configure($query, $criteria);
		}
		return $query;
	}
}