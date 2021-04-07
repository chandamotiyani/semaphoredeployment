<?php


namespace modules\Events\Elements;


use Craft;
use craft\base\Element;
use craft\commerce\base\Purchasable;
use craft\commerce\elements\Order;
use craft\commerce\models\LineItem;
use craft\elements\db\ElementQueryInterface;
use craft\helpers\DateTimeHelper;
use craft\helpers\UrlHelper;
use modules\Events\Elements\Db\EventQuery;
use modules\Events\Elements\Db\ScheduleQuery;
use modules\Events\Elements\Traits\Routing;
use modules\Events\Elements\Traits\Statuses;
use modules\Events\EventsModule;
use modules\Events\Models\EventGroupModel;
use modules\Events\Models\Records\ScheduleRecord;
use modules\Events\Models\Records\EventRecord as EventRecord;
use modules\Events\Models\ScheduleGroupModel;
use modules\Yalumba\Jobs\EventOrderCompleteJob;
use yii\base\InvalidConfigException;
use yii\db\ActiveQueryInterface;
use yii\db\Expression;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\validators\Validator;


class Schedule extends Purchasable {

    use Statuses;

    public $startDateTime; //datetime starts
    public $endDateTime; //datetime ends
    public $ticketsAvailable; // number of tickets currently available
    public $tickets; //total number of tickets available
    public $id; //puchasable id
    public $utcStartDateTime; //hack to allow for unadjusted retrieval of data
    public $groupId; // essentially this just holds the fieldLayouts
    public $eventId; // the event ID - makes it easier to do the reverse relationships that are required.
    public $noticeDateTime;
    public $price;
    public $s_id;
    public $e_id;
    public $scheduleCount;
    // Chanda - setting a column to show the nearest date in the future
    public $next_on;
    private $_event;


    /**
     * @inheritdoc
     */
    public function datetimeAttributes(): array
    {
        $attributes = parent::datetimeAttributes();
        $attributes[] = 'startDateTime';
        $attributes[] = 'endDateTime';
        $attributes[] = 'noticeDateTime';
        return $attributes;
    }

    /**
     * @inheritdoc
     */
    public static function find(): ElementQueryInterface {
        return new ScheduleQuery( static::class );
    }


    /**
     * @inheritdoc
     */
    public function getDescription(): string
    {
        if( empty($this->getEvent()->title ) ) {
            return '';
        }
        if(is_object($this->getStartDateTime())){
            $date = $this->getStartDateTime()->format('g:i a D dS M Y');
        }
        else{
            $date = $this->getStartDateTime();
        }
        return $this->getEvent()->title . ": ".$date;
    }


    /**
     *
     * @return array
     * @throws InvalidConfigException
     */
    public function getSnapshot(): array
    {
        $data = [];
        $data['description'] = $this->getDescription();
        $data['onSale'] = $this->getOnSale();
        // Chanda - check if event exist
        $data['title'] = $this->getEvent() ? $this->getEvent()->title : "";
        $data['url'] = $this->getEvent() ? $this->getEvent()->url : "";
        // end
        $data['startDateTime'] = $this->getStartDateTime()->format('Y-m-d H:i:s');
        // Chanda - check if event exist
        $data['groupHandle'] = $this->getEvent() ? $this->getEvent()->groupHandle : "";
        // end
        $data['noticeDateTime'] = $this->noticeDateTime;
        return $data;
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
        return 'Schedule';
    }

    /**
     * @inheritdoc
     */
    public static function pluralDisplayName(): string {
        return 'Schedule';
    }

    /**
     * @inheritdoc
     */
    public static function refHandle() {
        return 'schedule';
    }

    /**
     * @inheritdoc
     */
    public static function hasContent(): bool {
        return false;
    }

    /**
     * @inheritdoc
     */
    public static function hasTitles(): bool {
        return false;
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
     * @return ScheduleGroupModel
     * @throws InvalidConfigException if [[groupId]] is missing or invalid
     */
    public function getGroup(): ScheduleGroupModel {

        if ( $this->groupId === null ) {
            throw new InvalidConfigException( 'Schedule is missing its group ID' );
        }

        if ( ( $group = EventsModule::getInstance()->schedule->getScheduleGroupById( $this->groupId ) ) === null ) {
            throw new InvalidConfigException( 'Invalid schedule group ID: ' . $this->groupId );
        }

        return $group;
    }


    /**
     * @inheritdoc
     */
    protected static function defineTableAttributes(): array {
        $attributes = [
            'id'=>'ID',
            'startDateTime' =>"Start Date Time",
        ];

        return $attributes;
    }

    /**
     * @inheritdoc
     */
    public function getCpEditUrl() {
        return UrlHelper::cpUrl( 'events/schedule/' . $this->id . "/edit" );
    }


    /**
     * @inheritdoc
     */
    public function afterSave( bool $isNew ) {
        if ( $isNew ) {
            $record     = new ScheduleRecord();
            $record->id = (int) $this->id;
        }
        else{
            $record = ScheduleRecord::findOne($this->id);

            if (!$record) {
                throw new \Exception('Invalid schedule ID: ' . $this->id);
            }
        }
        $record->startDateTime = $this->startDateTime?$this->startDateTime:NULL;
        $record->endDateTime = $this->endDateTime?$this->endDateTime:NULL;
        $record->ticketsAvailable = $this->ticketsAvailable?$this->ticketsAvailable:NULL;
        $record->tickets = $this->tickets?$this->tickets:NULL;
        $record->groupId = $this->groupId?$this->groupId:NULL;
        $record->eventId = $this->eventId?$this->eventId:NULL;
        $record->noticeDateTime = $this->noticeDateTime?$this->noticeDateTime:NULL;

        $record->save();
        parent::afterSave( $isNew );
    }

    /**
     * @return mixed
     */
    public function getStartDateTime() {
        return $this->startDateTime;
    }

    /**
     * @return mixed
     */
    public function getEndDateTime() {
        return $this->endDateTime;
    }

    /**
     * @return mixed
     */
    public function getTicketsAvailable() {
        return $this->ticketsAvailable;
    }

    /**
     * @return mixed
     */
    public function getNoticeDateTime() {
        return $this->noticeDateTime;
    }

    /**
     * @return mixed
     */
    public function getTickets() {
        return $this->tickets;
    }

    /**
     * @return mixed
     */
    public function getGroupId() {
        return $this->groupId;
    }

    /**
     * @return mixed
     */
    public function getUtcStartDateTime() {
        return $this->utcStartDateTime;
    }

    protected static function defineSources( string $context = null ): array {

        $sources = [];

        $sources[] = [
            'key'      => '*',
            'label'    => 'All Schedules',
            'criteria' => [],
        ];

        return $sources;
    }



	public function getEvent(){

		if(!$this->_event || !isset($this->_event->id) || !$this->_event->id){
			$this->_event = Event::find()->relatedTo($this)->one();
		}
		return $this->_event;
	}



    /**
     * @inheritDoc
     */
    public function getPrice(): float {
        //TODO:DENORMALISE THIS
        if(!isset($this->price) || ($this->price == '')){
            if(isset($this->event[0])){
                $this->price = $this->event[0]->price;
            }
            else{
                $event = $this->getEvent();
                if(isset($event) && ($event->price != '')){
                    $this->price =  $event->price;
                }
            }
        }
        if(!$this->price){
            $this->price = 0;
        }
        return $this->price;
    }

    /**
     * @inheritDoc
     */
    public function getSku(): string {
        return uniqid();
    }

    public function populateLineItem(LineItem $lineItem)
    {

        if ($this->noticeDateTime && $this->noticeDateTime < new \DateTime() ) {
//          $lineItem->qty = 0;
//          we can remove the line item if we wanted.
//            throw new \Exception(Craft::t('commerce', 'Tickets to this event are no longer available for purchase.'));
        }

        if (($lineItem->qty > $this->ticketsAvailable)) {
            $lineItem->qty = $this->ticketsAvailable;
        }
    }

    public function getLineItemRules(LineItem $lineItem): array {
        $order = $lineItem->getOrder();

        // After the order is complete shouldn't check things like stock being available or the purchasable being around since they are irrelevant.
        if ( $order && $order->isCompleted ) {
            return [];
        }

        $getQty = function(LineItem $lineItem) {
            $qty = 0;
            foreach ($lineItem->getOrder()->getLineItems() as $item) {
                if ($item->id !== null && $item->id == $lineItem->id) {
                    $qty += $lineItem->qty;
                } elseif ($item->purchasableId == $lineItem->purchasableId) {
                    $qty += $item->qty;
                }
            }
            return $qty;
        };
        /** @var Purchasable $purchasable */
        $purchasable = $lineItem->getPurchasable();
        return [
            // an inline validator defined as an anonymous function
            [
                'purchasableId',
                function($attribute, $params, Validator $validator) use ($lineItem, $purchasable) {
                    if ($purchasable->getStatus() != self::$STATUS_UPCOMING) {
                        $validator->addError($lineItem, $attribute, Craft::t('commerce', 'Tickets to this event are no longer available for purchase.'));
                    }
                    // Chanda - webmaster account issue

                    if (!empty($purchasable->getEvent()) && $purchasable->getEvent()->getStatus() != Element::STATUS_ENABLED) {
                        $validator->addError($lineItem, $attribute, Craft::t('commerce', 'The event is not for sale at the moment.'));
                    }
                    if ($purchasable->noticeDateTime < new \DateTime() ) {
                        $validator->addError($lineItem, $attribute, Craft::t('commerce', 'Tickets to this event are no longer available for purchase.'));
                    }
                }
            ],
            [
                'qty',
                function($attribute, $params, Validator $validator) use ($lineItem, $getQty, $purchasable) {
                    if($purchasable->ticketsAvailable < $getQty($lineItem)){
                        $description = $lineItem->purchasable->getEvent()->title." at ".$lineItem->purchasable->getStartDateTime()->format('H:i:s D dS M Y');
                        if($this->ticketsAvailable){
                            $error = "There are only $this->ticketsAvailable tickets left for $description";
                        }
                        else{
                            $error = "There are no tickets left for $description";
                        }
                        $validator->addError($lineItem, $attribute, $error);
                    }
                }
            ]
        ];
    }

    public function afterOrderComplete(Order $order, LineItem $lineItem) {
        //send off the line item to rezdy to mark it as completed.
        Craft::$app->queue->push(new EventOrderCompleteJob([ 'orderId' =>$order->id, 'lineItemId'=>$lineItem->id]));
    }

    public function getPromotionRelationSource(): array {
        // Chanda - check if event exist else return empty array
        if($this->getEvent()) {
            $event_id = $this->getEvent()->id;
            return [$this->id, $event_id];
        }
        return [];
        // end
    }

    /**
     * @inheritdoc
     */
    public function hasFreeShipping(): bool
    {
        return true;
    }

    public function getIsShippable(): bool
    {
        return false;
    }
}
