<?php


namespace modules\Importer\Importers;


use craft\helpers\Db;
use modules\Events\Elements\Event;
use modules\Events\Elements\Schedule;
use modules\Events\EventsModule;
use modules\Importer\Contracts\Importer;
use modules\Importer\Contracts\EventsImporter;
use modules\Importer\ImporterModule;
use modules\Importer\Traits\HasImportIdentifier;
use modules\Importer\Traits\HasLog;
use modules\Rezdy\Models\Records\APIEventRecord;
use modules\Rezdy\Models\Records\APIEventScheduleRecord;
use modules\Rezdy\RezdyModule;

/**
 * Imports event schedules (ie dates and times) from Rezdy. Runs as part of a cron and also
 * as part of saving events from the craft admin.
 *
 * Class EventScheduleImporter
 * @package modules\Importer\Importers
 */
class EventScheduleImporter implements Importer, EventsImporter {

    use HasLog, HasImportIdentifier;

    // ATTENTION: this doesn't need to run from the cron since it will work off the
    // event update hook.. when an event is updated, it will automatically run this
    // to pull in any schedules

    private $events;

    // this sets whether we need to save the event back or if
    // something else will do it for us
    private $withoutEventSave = false;

    protected static $importerIdentifier = 'event-schedule';

    private function getEvents(){
        if($this->events){
            if(is_array($this->events)){
                return $this->events;
            }
            else{
                return [$this->events];
            }
        }
        return Event::find()->where(['not', ['apiId'=>'']] )->all();
    }

    public function setEvents($events):EventsImporter{
        $this->events = $events;
        return $this;
    }

    public function withoutEventSave():EventsImporter{
        $this->withoutEventSave = true;
        return $this;
    }

    public function import() {
        $events = $this->getEvents();
        $scheduleGroup = EventsModule::getInstance()->schedule->getScheduleGroupByHandle('default');
        $eventsApiIdArray = [];
        foreach($events as $x){
            if($x->apiId){
                $eventsApiIdArray[] = $x->apiId;
            }
        }

       /* $options = [
            'events'=>$eventsApiIdArray,
            'startTime'=>new \DateTime(),
            'endTime'=>(new \DateTime())->add(new \DateInterval('P1Y'))
        ];

        $this->log("Retrieving Rezdy Availability");

        $response = RezdyModule::getInstance()->api->getRezdyAvailability($options);*/
        //$currentSchedule = $eventElement->schedule->all();
        $this->log("Rezdy Availability Retrieved. Beginning Processing for ".count($events)." existing events");

        foreach($events as $eventElement){

            $this->log("Processing for event $eventElement->id");

            $currentSchedule = Schedule::find()->relatedTo(($eventElement))->all();

            $newScheduleList = [];

            $options = [
                'events'=>$eventElement->apiId,
                //Chanda - commenting following because previously it was looking for the current day to fetch events
                //'startTime'=>new \DateTime(),
                //Chanda - now taking events from last 6 months because the there are orders in June which has not order number generated (P.S taking more load)
                'startTime'=>(new \DateTime())->modify('-1 month'),
                'endTime'=>(new \DateTime())->add(new \DateInterval('P1Y'))
            ];

            $this->log("Retrieving Rezdy Availability");

            $response = RezdyModule::getInstance()->api->getRezdyAvailability($options);

            if($response->api_response_body->requestStatus->success) {
                //get the schedule just for this event (the api query returns all in the result set)
               /* $apiEventSchedules = array_filter($response->getData(), function($schedule) use ($eventElement){
                    return $schedule->productCode == $eventElement->apiId;
                });*/

                $apiEventSchedules = $response->api_response_body->sessions;

                //we need to create the schedule elements and put the id's into an array for saving
                foreach ($apiEventSchedules as $apiEventSchedule) {

                    $startDateTime = \DateTime::createFromFormat('Y-m-d H:i:s', $apiEventSchedule->startTimeLocal);
                    $endDateTime = \DateTime::createFromFormat('Y-m-d H:i:s', $apiEventSchedule->endTimeLocal);

                    if ($eventElement->minimumNotice) {
                        $noticeDateTime = \DateTime::createFromFormat('Y-m-d H:i:s', $apiEventSchedule->startTimeLocal);
                        $noticeDateTime->sub(new \DateInterval("PT{$eventElement->minimumNotice}H"));
                    } else {
                        $noticeDateTime = $startDateTime;
                    }

                    // search to see if this schedule already exists as an element
                    if ($currentSchedule) {
                        $existing = array_filter($currentSchedule, function ($item) use ($apiEventSchedule) {

                            if (is_a($item->startDateTime, 'DateTime')) {
                                if ($apiEventSchedule->startTimeLocal == $item->startDateTime->format('Y-m-d H:i:s')) {
                                    return true;
                                }
                            } else {

                            }
                        });

                    }

                    if (isset($existing) && is_array($existing) && count($existing)) {
                        $scheduleElement = reset($existing);
                    } else {
                        $scheduleElement = new Schedule();
                    }

                    $scheduleElement->startDateTime = Db::prepareDateForDb($startDateTime);
                    $scheduleElement->endDateTime = Db::prepareDateForDb($endDateTime);
                    $scheduleElement->noticeDateTime = Db::prepareDateForDb($noticeDateTime);
                    $scheduleElement->tickets = $apiEventSchedule->seats;
                    $scheduleElement->ticketsAvailable = $apiEventSchedule->seatsAvailable;
                    $scheduleElement->groupId = $scheduleGroup->id;
                    $scheduleElement->eventId = $eventElement->id;
                    if(\Craft::$app->elements->saveElement($scheduleElement, false, true, false)) {
                        $this->log("Schedule $scheduleElement->id saved");
                        $newScheduleList[] = $scheduleElement->id;
                    }

                }
                $eventElement->schedule = $newScheduleList;

//			//delete the remaining schedules since they are no longer in rezdy.
                if(@$currentSchedule){
                    foreach($currentSchedule as $scheduleItemBeforeImport){

                        //items that should be deleted
                        $scheduleElementsToDelete = array_filter($currentSchedule, function ($item) use($scheduleItemBeforeImport){
                            if(!$scheduleItemBeforeImport->startDateTime){
                                return true; //if it doesn't have a start time - delete it. validation should pick this up.
                            }
                            if(!is_string($scheduleItemBeforeImport->startDateTime)){
                                $scheduleItemBeforeImportStart = $scheduleItemBeforeImport->startDateTime->format('Y-m-d H:i:s');
                            }
                            else{
                                $scheduleItemBeforeImportStart = $scheduleItemBeforeImport->startDateTime;
                            }

                            if(!is_string($item->startDateTime)){
                                $itemStart = $item->startDateTime->format('Y-m-d H:i:s');
                            }
                            else{
                                $itemStart = $item->startDateTime;
                            }

                            if($itemStart == $scheduleItemBeforeImportStart){
                                return true;
                            }
                        });

                        foreach($scheduleElementsToDelete as $scheduleElementToDelete){
                            if(!isset($scheduleElement) || !$scheduleElement){
                                $this->log("Deleting old schedule $scheduleElementToDelete->id");

                                \Craft::$app->elements->deleteElement($scheduleElementToDelete);
                            }
                        }
                    }
                }

                $eventElement->setFieldValue('schedule', $newScheduleList);

                if($this->withoutEventSave){
                    return $eventElement;
                }
                else{
                    \Craft::$app->elements->saveElement($eventElement, false, true, false);
                    $this->log("Saved schedules to event");
                }
            } else {
                $this->log("Could not process for ". $eventElement->apiId. " due to ".$response->api_response_body->requestStatus->error->errorMessage);
            }


        }
    }
}
