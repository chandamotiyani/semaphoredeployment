<?php
namespace modules\Events\Controllers;

use craft\helpers\UrlHelper;
use modules\Events\EventsModule;
use modules\Events\EventsAssetBundle;
use modules\Events\EventsEditAssetBundle;
use modules\Events\Models\EventGroupModel;
use modules\Events\Elements\Event;


use Craft;
use craft\web\Controller;
use modules\Events\Models\Records\EventRecord;use modules\Importer\Importers\EventScheduleImporter;
use modules\Rezdy\Fields\RezdyId;
use yii\base\Exception;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class EventsController extends Controller{
	protected $allowAnonymous = ['register', 'login'];

	public function actionIndex($eventGroupHandle = '') {
		$this->getView()->registerAssetBundle(EventsAssetBundle::class);
		return $this->renderTemplate('events/index', [
			'eventGroupHandle'=>$eventGroupHandle
		]);
	}

	/**
	 * @return \yii\web\Response
	 * @throws \craft\errors\MissingComponentException
	 * @throws \yii\base\Exception
	 * @throws \yii\web\BadRequestHttpException
	 * @throws \yii\web\ForbiddenHttpException
	 */
	public function actionFieldLayout() {
		if (Craft::$app->request->isPost) {
			$this->requirePostRequest();
			$this->requireAdmin();
			// Set the field layout
			$fieldLayout = Craft::$app->getFields()->assembleLayoutFromPost();
			$fieldLayout->type = Event::class;
			if (!Craft::$app->getFields()->saveLayout($fieldLayout)) {
				Craft::$app->getSession()->setError(Craft::t('app', 'Couldn’t save event fields.'));
				return $this->redirectToPostedUrl();
			}
			Craft::$app->getSession()->setNotice(Craft::t('app', 'event fields saved.'));
			return $this->redirectToPostedUrl();
		} else if (Craft::$app->request->isGet) {

			$fieldLayout = Craft::$app->getFields()->getLayoutByType(Event::class);
			return $this->renderTemplate('events/field-layout',['fieldLayout'=>$fieldLayout ]);
		}
	}

	public function actionDelete(){
		//TODO.
	}

	/**
	 * @param $eventGroupHandle
	 *
	 * @return Response
	 * @throws Exception
	 * @throws \Throwable
	 * @throws \craft\errors\ElementNotFoundException
	 */
	public function actionNew($eventGroupHandle) {
		$this->getView()->registerAssetBundle(EventsEditAssetBundle::class);
		$variables = [];
        $variables['continueEditingUrl'] = 'events/{id}/edit';
		$variables['event'] = new Event();
		$eventGroup = EventsModule::getInstance()->events->getEventGroupByHandle($eventGroupHandle);
		$variables['event']->groupId = $eventGroup->id;
		$variables['event']->groupHandle = $eventGroup->handle;
		$variables['tabs'] = $this->_getTabs($variables['event']);
        $variables['showPreviewBtn'] = false;
		$apiField = new RezdyId([
			'name'=>'apiId',
			'id'=>'apiId-dropdown'
		]);
		$variables['rezdyField'] = $apiField;
		if (Craft::$app->request->isPost) {
			if ($variables['event']) {
                $variables = $this->updateFields($variables);
                // Chanda start - custom validation for rezdy event and event booking url if the price is > 0

                $enabled = $this->checkRazdyBookingurl($variables['event']);
                $variables['event']['enabled'] = empty($enabled) ? 0 : 1;

                // Chanda end - this will validate the required fields set in the fieldlayout design of craft

                // Chanda Vyas start - 2nd July 2020
                $variables['event']->setScenario(\craft\base\Element::SCENARIO_LIVE);
                // Chanda Vyas end - 2nd July 2020

                if (Craft::$app->elements->saveElement($variables['event'], false, true, false)) {
                    if  ($this->checkRazdyBookingurl($variables['event'])) {
                        Craft::$app->session->setNotice("Event has been updated");
                        //$this->redirect(UrlHelper::cpUrl('events'));
                        return $this->redirectToPostedUrl($variables['event']);

                    } else {
                        Craft::$app->session->setNotice("Please select a Rezdy Event in the right panel, or enter the Event Booking URL");
                        return $this->redirectToPostedUrl($variables['event']);
                    }
				} else {
					Craft::$app->session->setError("The event cannot be created");
					return $this->renderTemplate('events/edit', $variables);
				}
			} else {
				throw new Exception('event not found.');
			}
		} else if (Craft::$app->request->isGet) {
			return $this->renderTemplate('events/edit', $variables);
		}
	}

	public function actionEdit($id = NULL) {
        $validation_bool = false;
		$this->getView()->registerAssetBundle(EventsEditAssetBundle::class);
		if(!$id){
			$id = Craft::$app->request->getParam('id');
		}
		$variables = [];
        $variables['event'] = Event::find()->anyStatus()->where(['yalumba_events.id'=>$id])->one();
		$variables['tabs'] = $this->_getTabs($variables['event']);
		$apiField = new RezdyId([
			'name'=>'apiId',
			'id'=>'apiId-dropdown'
		]);
        $variables['continueEditingUrl'] = 'events/{id}/edit';
        $variables['shareUrl'] = $variables['event']->getUrl();
        $variables['showPreviewBtn'] = false;
		$variables['rezdyField'] = $apiField;

		if (Craft::$app->request->isPost) {
			if ($variables['event']) {
				$variables = $this->updateFields($variables);
                // Chanda start - custom validation for rezdy event and event booking url if the price is > 0

                $enabled = $this->checkRazdyBookingurl($variables['event']);
                $variables['event']['enabled'] = empty($enabled) ? 0 : 1;

                // following will validate the required fields set in the fieldlayout design of craft
                // 2nd July 2020
                $variables['event']->setScenario(\craft\base\Element::SCENARIO_LIVE);
                // Chanda Vyas end - 2nd July 2020

				if (Craft::$app->elements->saveElement($variables['event'], false, true, false)) {
                    if  ($this->checkRazdyBookingurl($variables['event'])) {
                        Craft::$app->session->setNotice("Event has been updated");
                        //$this->redirect(UrlHelper::cpUrl('events'));
                        return $this->redirectToPostedUrl($variables['event']);

                    } else {
                        Craft::$app->session->setNotice("Please select a Rezdy Event in the right panel, or enter the Event Booking URL");
                        return $this->redirectToPostedUrl($variables['event']);
                    }
				} else {
					Craft::$app->session->setError("The event cannot be updated");
					return $this->renderTemplate('events/edit', $variables);
				}
			} else {
				throw new Exception('Event not found.');
			}
		} else if (Craft::$app->request->isGet) {
			if (@$variables['event']) {
				return $this->renderTemplate('events/edit', $variables);
			} else {
				throw new Exception('Event not found.');
			}
		}
	}

	private function updateFields($variables){
		$variables['event']->slug = Craft::$app->getRequest()->getBodyParam('slug', $variables['event']->slug);
		$variables['event']->title = Craft::$app->getRequest()->getBodyParam('title', $variables['event']->title);
		$variables['event']->price = Craft::$app->getRequest()->getBodyParam('price', $variables['event']->price);
		$variables['event']->sku = Craft::$app->getRequest()->getBodyParam('sku', $variables['event']->sku);
		$variables['event']->apiId = Craft::$app->getRequest()->getBodyParam('apiId', $variables['event']->apiId);
		if($variables['event']->apiId){
			$variables['event']->apiType = 'rezdy';
		}
		else{
			$variables['event']->apiType = '';
		}
		$variables['event']->groupHandle = Craft::$app->getRequest()->getBodyParam('groupHandle', $variables['event']->groupHandle);
		$variables['event']->groupId = Craft::$app->getRequest()->getBodyParam('groupId', $variables['event']->groupId);

		$fieldsLocation = Craft::$app->getRequest()->getParam('fieldsLocation', 'fields');
		$fields = Craft::$app->getRequest()->getBodyParam($fieldsLocation);
		$variables['event']->setFieldValuesFromRequest($fieldsLocation);

		$variables = $this->_updateSchedulesAndFields($variables, $fields);

		return $variables;
	}

	private function _updateSchedulesAndFields($variables, $fields){
		if($variables['event']->apiType == 'rezdy'){
			//TODO: we need to get the schedules using the importer:
			$eventImporter = new EventScheduleImporter();
			$variables['event'] = $eventImporter->withoutEventSave()->setEvents($variables['event'])->import();

		}else{

			//and we have to save the schedules:
			//DELETE SCHEDULES
			if( isset(Craft::$app->getRequest()->getBodyParam('fields', [])['schedulesToDelete'])){
				$toDelete = Craft::$app->getRequest()->getBodyParam('fields', [])['schedulesToDelete'];
				EventsModule::getInstance()->schedule->deleteScheduleByIds($toDelete);
			}

			//ADD/UPDATE SCHEDULES:
			$updatedSchedules = EventsModule::getInstance()->schedule->addAndUpdateSchedules(
				Craft::$app->getRequest()->getBodyParam('newSchedule', []),
				isset($fields['schedule'])?$fields['schedule']:[],
                $variables['event']
			);

			$scheduleArray = [];
			foreach($updatedSchedules as $updatedSchedule) {
				$scheduleArray[] = $updatedSchedule->id;
			}
			$variables['event']->setFieldValue('schedule', $scheduleArray);
		}
		return $variables;
	}

	/**
	 * works out what tabs we need on the edit/new pages
	 * @param $event
	 */
	private function _getTabs($event){
		$tabs = [];
		$hasErrors = false;
		foreach ($event->getFieldLayout()->getTabs() as $index => $tab) {
			// Do any of the fields on this tab have errors?

			if ($event->hasErrors()) {
				foreach ($tab->getFields() as $field) {
					if ($hasErrors = $event->hasErrors($field->handle . '.*')) {
						break;
					}
				}
			}

			$tabs[] = [
				'label' => Craft::t('site', $tab->name),
				'url' => '#' . $tab->getHtmlId(),
				'class' => $hasErrors ? 'error' : null
			];
		}
		return $tabs;
	}

	public function actionGroups(): Response
	{
		$this->requireAdmin();

		$eventGroups = \modules\Events\Models\Records\EventGroupRecord::find()->all();

		return $this->renderTemplate('events/groups/index', [
			'eventGroups' => $eventGroups
		]);
	}


	/**
	 * NEW/Edit an event group.
	 *
	 * @param int|null $eventGroupId The event group’s ID, if any.
	 * @param EventGroupModel|null $eventGroup The event group being edited, if there were any validation errors.
	 *
	 * @return Response
	 * @throws NotFoundHttpException if the requested event group cannot be found
	 */
	public function actionEditEventGroup(int $eventGroupId = null, EventGroupModel $eventGroup = null): Response
	{
		$this->requireAdmin();

		if ($eventGroupId !== null) {
			if ($eventGroup === null) {
				$eventGroup = EventsModule::getInstance()->events->getEventGroupById($eventGroupId);
				if (!$eventGroup) {
					throw new NotFoundHttpException('Event group not found');
				}
			}

			$title = trim($eventGroup->name) ?: Craft::t('app', 'Edit Event Group');
		} else {
			if ($eventGroup === null) {
				$eventGroup = new EventGroupModel();
			}

			$title = Craft::t('app', 'Create a new event group');
		}

		// Breadcrumbs
		$crumbs = [
			[
				'label' => Craft::t('app', 'Settings'),
				'url' => UrlHelper::url('settings')
			],
			[
				'label' => Craft::t('app', 'Events Groups'),
				'url' => UrlHelper::url('events/group')
			]
		];

		// Tabs
		$tabs = [
			'settings' => [
				'label' => Craft::t('app', 'Settings'),
				'url' => '#eventgroup-settings'
			],
			'fieldLayout' => [
				'label' => Craft::t('app', 'Field Layout'),
				'url' => '#eventgroup-fieldlayout'
			]
		];

		return $this->renderTemplate('events/groups/_edit', [
			'eventGroupId' => $eventGroupId,
			'eventGroup' => $eventGroup,
			'title' => $title,
			'crumbs' => $crumbs,
			'tabs' => $tabs
		]);
	}


	/**
	 * Save a event group.
	 *
	 * @return Response|null
	 */
	public function actionSaveEventGroup()
	{
		$this->requirePostRequest();
		$this->requireAdmin();

		$eventGroup = new EventGroupModel();

		// Set the simple stuff
		$eventGroup->id = Craft::$app->getRequest()->getBodyParam('eventGroupId');
		$eventGroup->name = Craft::$app->getRequest()->getBodyParam('name');
		$eventGroup->handle = Craft::$app->getRequest()->getBodyParam('handle');

		// Set the field layout
		$fieldLayout = Craft::$app->getFields()->assembleLayoutFromPost();
		$fieldLayout->type = Event::class;
		$eventGroup->setFieldLayout($fieldLayout);

		// Save it
		if (!EventsModule::getInstance()->events->saveEventGroup($eventGroup)) {
			Craft::$app->getSession()->setError(Craft::t('app', 'Couldn’t save the event group.'));

			// Send the tag group back to the template
			Craft::$app->getUrlManager()->setRouteParams([
				'eventGroup' => $eventGroup
			]);

			return null;
		}

		Craft::$app->getSession()->setNotice(Craft::t('app', 'Event group saved.'));

		return $this->redirectToPostedUrl($eventGroup);
	}

	/**
	 * @return Response
	 * @throws \yii\web\BadRequestHttpException
	 * @throws \yii\web\ForbiddenHttpException
	 */
	public function actionDeleteEventGroup() {
		$this->requirePostRequest();
		$this->requireAcceptsJson();
		$this->requireAdmin();

		$groupId = Craft::$app->getRequest()->getRequiredBodyParam('id');

		if( EventsModule::getInstance()->events->deleteEventGroupById($groupId)){
			return $this->asJson(['success' => true]);
		}
		return $this->asJson(['success' => false]);
	}

    /**
     * @param $event
     * THis function will return bool value as per the custom validation
     * description of custom validation - if the price is greater than 0 and razdy event and event booking url bothe are empty then it will return true or false accordingly
     */
	public function checkRazdyBookingurl($event) {
        if(empty($event['eventBookingUrl']) && empty($event['apiId']) && ($event['price'] > 0)) {
            return false;
        } else {
            return true;
        }
    }
}