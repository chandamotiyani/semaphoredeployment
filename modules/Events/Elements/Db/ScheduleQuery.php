<?php
namespace modules\Events\Elements\Db;

use craft\elements\db\ElementQuery;
use modules\Events\Elements\Event;
use modules\Events\EventsModule;

class ScheduleQuery extends ElementQuery {

	public $startDateTime;
	public $endDateTime;
	public $sku;
	public $utcStartDateTime;
	public $ticketsAvailable;
	public $tickets;
	public $groupId;
	public $price;
    public $eventId;
    public $noticeDateTime;

	public function ticketsAvailable($value)
	{
		$this->ticketsAvailable = $value;
		return $this;
	}

    public function noticeDateTime($value)
    {
        $this->noticeDateTime = $value;
        return $this;
    }

	public function tickets($value)
	{
		$this->tickets = $value;
		return $this;
	}

	public function groupId($value)
	{
		$this->groupId = $value;
		return $this;
	}

    public function eventId($value)
    {
        $this->eventId = $value;
        return $this;
    }

	public function utcStartDateTime($value)
	{
		$this->utcStartDateTime = $value;
		return $this;
	}

	public function startDateTime($value)
	{
		$this->startDateTime = $value;
		return $this;
	}

	public function price($value)
	{
		$this->price = $value;
		return $this;
	}

	public function endDateTime($value)
	{
		$this->endDateTime = $value;
		return $this;
	}


	protected function beforePrepare(): bool
	{
		$timeZoneUTC = new \DateTimeZone("UTC");
		$timeZoneFromConfig = new \DateTimeZone(\Craft::$app->getTimeZone());
		$dateTimeFromUTC = new \DateTime("now", $timeZoneUTC);
		$dateTimeFromConfig = new \DateTime("now", $timeZoneFromConfig);
		$timeOffset = $timeZoneFromConfig->getOffset($dateTimeFromUTC);
		if($timeOffset>=0){
			$off = "+".gmdate("H:i",($timeOffset));
		}
		else{
			$off = "-".gmdate("H:i",($timeOffset));
		}

		$this->joinElementTable('yalumba_eventschedule');

		// select all the columns
		$this->query->select([
			'yalumba_eventschedule.endDateTime',
			'yalumba_eventschedule.startDateTime',
			'yalumba_eventschedule.ticketsAvailable',
			'yalumba_eventschedule.tickets',
			'yalumba_eventschedule.groupId',
			'yalumba_eventschedule.price',
            'yalumba_eventschedule.eventId',
            'yalumba_eventschedule.noticeDateTime',
			'CONVERT_TZ(`yalumba_eventschedule`.startDateTime, "+00:00", "'.$off.'") AS utcStartDateTime',
		]);



		$this->subQuery = $this->subQuery->addSelect([
			'CONVERT_TZ(`yalumba_eventschedule`.startDateTime, "+00:00", "'.$off.'") AS utcStartDateTime',
		]);

		if ($this->utcStartDateTime) {
			$this->subQuery->andWhere(Db::parseParam('utcStartDateTime', $this->utcStartDateTime));
		}

		if ($this->endDateTime) {
			$this->subQuery->andWhere(Db::parseParam('yalumba_eventschedule.endDateTime', $this->endDateTime));
		}

        if ($this->noticeDateTime) {
            $this->subQuery->andWhere(Db::parseParam('yalumba_eventschedule.noticeDateTime', $this->noticeDateTime));
        }

        if ($this->eventId) {
            $this->subQuery->andWhere(Db::parseParam('yalumba_eventschedule.eventId', $this->eventId));
        }

		if ($this->groupId) {
			$this->subQuery->andWhere(Db::parseParam('yalumba_eventschedule.groupId', $this->groupId));
		}

		if ($this->ticketsAvailable) {
			$this->subQuery->andWhere(Db::parseParam('yalumba_eventschedule.ticketsAvailable', $this->ticketsAvailable));
		}

		if ($this->price) {
			$this->subQuery->andWhere(Db::parseParam('yalumba_eventschedule.price', $this->price));
		}

		if ($this->tickets) {
			$this->subQuery->andWhere(Db::parseParam('yalumba_eventschedule.tickets', $this->tickets));
		}

		if ($this->startDateTime) {
			$this->subQuery->andWhere(Db::parseParam('yalumba_eventschedule.startDateTime', $this->startDateTime));
		}
		if($this->having){
			$this->subQuery->andHaving($this->having);
		}

		return parent::beforePrepare();
	}
}