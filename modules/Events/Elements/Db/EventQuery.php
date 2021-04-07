<?php
namespace modules\Events\Elements\Db;

use craft\db\QueryAbortedException;
use craft\elements\db\ElementQuery;
use craft\db\Query;
use craft\helpers\Db;
use craft\models\Section;
use modules\Events\Models\Records\ScheduleRecord;
use yii\db\ActiveQueryInterface;

class EventQuery extends ElementQuery {

	public $param;
	public $sku;
	public $price;
	public $groupId;
	public $groupHandle;
	public $schedule;
	public $apiType;
	public $apiId;
	public $dateCreated;

	public function sku($value)
	{
		$this->sku = $value;

		return $this;
	}

	public function dateCreated($value){
	    $this->dateCreated = $value;
	    return $this;
    }

	public function price($value)
	{
		$this->price = $value;

		return $this;
	}

	public function groupId($value)
	{
		$this->groupId = $value;

		return $this;
	}

	public function groupHandle($value)
	{
		$this->groupHandle = $value;

		return $this;
	}

	/**
	 * Returns the event group’s events.
	 *
	 * @return ActiveQueryInterface The relational query object.
	 */
	public function schedule($value) {
		$this->schedule = $value;

		return $this;
	}

	protected function beforePrepare(): bool
	{
		// join in the events table - seems to automagically changed into '{{%yalumba_events}}'
		$this->joinElementTable('yalumba_events');

		// select all the columns
		$this->query->select([
			'yalumba_events.price',
			'yalumba_events.groupId',
			'yalumba_events.groupHandle',
			'yalumba_events.apiType',
			'yalumba_events.apiId',
            'yalumba_events.dateCreated'
		]);

		//This limits us based on the group selection.
		if ($this->groupId) {
			$this->subQuery->andWhere(Db::parseParam('yalumba_events.groupId', $this->groupId));
		}

        if ($this->dateCreated) {
            $this->subQuery->andWhere(Db::parseParam('yalumba_events.dateCreated', $this->dateCreated));
        }

		if ($this->groupHandle) {
			$this->subQuery->andWhere(Db::parseParam('yalumba_events.groupHandle', $this->groupHandle));
		}

		if ($this->price) {
			$this->subQuery->andWhere(Db::parseParam('yalumba_events.price', $this->price));
		}

		return parent::beforePrepare();
	}
}