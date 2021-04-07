<?php
namespace modules\Events\Controllers;

use craft\helpers\ArrayHelper;
use craft\helpers\UrlHelper;
use modules\Events\EventsModule;
use modules\Events\EventsAssetBundle;
use modules\Events\EventsEditAssetBundle;
use modules\Events\Models\EventGroupModel;
use modules\Events\Elements\Event;
use modules\Events\Elements\Schedule;
use modules\Events\Services\ScheduleService;

use Craft;
use craft\web\Controller;
use yii\base\Exception;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class CalendarController extends Controller {

	protected $allowAnonymous = ['event-filter', 'search'];

	/**
	 * Gets all Events filters by params in request
	 * - includes schedule info for start & end times.
	 *
	 * @return JSON
	 */
	public function actionEventFilter($eventGroupHandle = NULL) {

		$out = [];

		$filter = $this->getFiltersFromRequest();

		$schedules = EventsModule::getInstance()->schedule->filter($filter, $eventGroupHandle)->all();

		foreach($schedules as $schedule) {
			$event = Event::find()->withPermission()->with(['bannerImage', 'eventLocation'])->relatedTo($schedule)->one(); // TODO: n+1 :( - Eager loading hasn't been setup for Schedules yet.
			$out[] = [
				's_id'=>$schedule->id,
				'id'=>$event->id,
				'url'=>$event->getUrl(),
				'title'=>$event->title,
				'start'=> $schedule->startDateTime->format('Y/m/d H:i:s'),
				'end'=>$schedule->endDateTime ? $schedule->endDateTime->format('Y/m/d H:i:s') : $schedule->startDateTime->format('Y-M-d'),
				'image'=>$event->bannerImage ? $event->bannerImage[0]->url : '',
				'price'=>$event->price,
				'sku'=>$event->sku,
				'location'=>$event->eventLocation ? $event->eventLocation[0]->title : '',
				'categoryId'=>1,
				'utcStartDateTime'=>$schedule->utcStartDateTime,
				'ticketsAvailable'=>$schedule->ticketsAvailable,
				'tickets'=>$schedule->tickets,
				'startTime' => $schedule->startDateTime->format('h:ia'),
				'endTime' => $schedule->endDateTime ? $schedule->endDateTime->format('h:ia') : '',
				'endDateTime' => $schedule->endDateTime ? $schedule->endDateTime->format('Y-m-d H:i:s') : '',
				'startDateTime' => $schedule->startDateTime->format('Y-m-d H:i:s'),
				'startFormatted' => $schedule->startDateTime->format('D d F'),
				'ticketTypeText' => isset($event->ticketTypeText) ? $event->ticketTypeText : 'per customer',
			];
		}

		Craft::$app->response->format = \yii\web\Response::FORMAT_JSON;
		return $out;
	}


	public function actionSearch($eventGroupHandle = NULL) {

		$out = [];

		$filter = $this->getFiltersFromRequest();

		$schedules = EventsModule::getInstance()->schedule->filter($filter, $eventGroupHandle)->all();

		foreach($schedules as $schedule) {
			$event = Event::find()->withPermission()->with(['bannerImage', 'eventLocation'])->relatedTo($schedule)->one(); // TODO: n+1 :( - Eager loading hasn't been setup for Schedules yet.
			$out[] = [
				'id'=>$event->id,
			];
		}

		Craft::$app->response->format = \yii\web\Response::FORMAT_JSON;
		return $out;
	}

	/**
	 * Transform request parameters into a format we can
	 * use with: ScheduleService::filter($filter, '')
	 */
	private function getFiltersFromRequest() {
		$requestParams = Craft::$app->request->getQueryParams();

		$filter = [];

		foreach($requestParams as $key => $paramValue) {
			$filter[] = ['param' => $paramValue, 'handle' => $key];
		}

		return $filter;
	}
}