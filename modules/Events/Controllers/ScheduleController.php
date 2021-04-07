<?php


namespace modules\Events\Controllers;


use craft\helpers\UrlHelper;
use craft\web\Controller;
use modules\Events\Elements\Event;
use modules\Events\Elements\Schedule;
use \Exception;
use Craft;
use modules\Events\EventsModule;
use modules\Events\Models\EventGroupModel;
use modules\Events\Models\Records\ScheduleGroupRecord;
use modules\Events\Models\ScheduleGroupModel;
use modules\Rezdy\Responses\Contracts\Response;
use yii\web\NotFoundHttpException;

class ScheduleController extends Controller{

	public function actionIndex($eventGroupHandle = '') {
		//TODO: pass it the eventHandle
		return $this->renderTemplate('events/schedule/index');
	}

	public function actionEdit($id = NULL) {
		if(!$id){
			$id = \Craft::$app->request->getParam('id');
		}
		$variables = [];
		$variables['schedule'] = Schedule::findOne($id);
		$variables['tabs'] = $this->_getTabs($variables['schedule']);
		if (Craft::$app->request->isPost) {
			if ($variables['schedule']) {

				$fieldsLocation = Craft::$app->getRequest()->getParam('fieldsLocation', 'fields');
				$variables['schedule']->setFieldValuesFromRequest($fieldsLocation);

				if (Craft::$app->elements->saveElement($variables['schedule'], false, true, false)) {
					Craft::$app->session->setNotice("Schedule has been updated");
					$this->redirect(UrlHelper::cpUrl('events/schedule'));
				} else {
					Craft::$app->session->setError("The schedule cannot be updated");
					return $this->renderTemplate('events/schedule/edit', $variables);
				}
			} else {
				throw new Exception('Schedule not found.');
			}
		} else if (Craft::$app->request->isGet) {
			if (@$variables['schedule']) {
				return $this->renderTemplate('events/schedule/edit', $variables);
			} else {
				throw new Exception('Schedule not found.');
			}
		}
	}

	/**
	 * works out what tabs we need on the edit/new pages
	 * @param $schedule
	 */
	private function _getTabs($schedule){
		$tabs = [];
		$hasErrors = false;
		foreach ($schedule->getFieldLayout()->getTabs() as $index => $tab) {
			// Do any of the fields on this tab have errors?

			if ($schedule->hasErrors()) {
				foreach ($tab->getFields() as $field) {
					if ($hasErrors = $schedule->hasErrors($field->handle . '.*')) {
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


	/**
	 *  NEW/Edit a schedule group.
	 * @param int|null $scheduleGroupId
	 * @param ScheduleGroupModel|null $scheduleGroup
	 *
	 * @return Response
	 * @throws \yii\web\ForbiddenHttpException
	 */
	public function actionEditScheduleGroup(int $scheduleGroupId = null, ScheduleGroupModel $scheduleGroup = null): \yii\web\Response
	{
		$this->requireAdmin();

		if ($scheduleGroupId !== null) {
			if ($scheduleGroup === null) {
				$scheduleGroup = EventsModule::getInstance()->schedule->getScheduleGroupById($scheduleGroupId);
				if (!$scheduleGroup) {
					throw new NotFoundHttpException('Schedule group not found');
				}
			}

			$title = trim($scheduleGroup->name) ?: Craft::t('app', 'Edit Schedule Group');
		} else {
			if ($scheduleGroup === null) {
				$scheduleGroup = new ScheduleGroupModel();
			}

			$title = Craft::t('app', 'Create a new schedule group');
		}

		// Breadcrumbs
		$crumbs = [
			[
				'label' => Craft::t('app', 'Settings'),
				'url' => UrlHelper::url('settings')
			],
			[
				'label' => Craft::t('app', 'Schedule Groups'),
				'url' => UrlHelper::url('events/schedule/group')
			]
		];

		// Tabs
		$tabs = [
			'settings' => [
				'label' => Craft::t('app', 'Settings'),
				'url' => '#schedulegroup-settings'
			],
			'fieldLayout' => [
				'label' => Craft::t('app', 'Field Layout'),
				'url' => '#schedulegroup-fieldlayout'
			]
		];

		return $this->renderTemplate('events/schedule/groups/_edit', [
			'scheduleGroupId' => $scheduleGroupId,
			'scheduleGroup' => $scheduleGroup,
			'title' => $title,
			'crumbs' => $crumbs,
			'tabs' => $tabs
		]);
	}

	/**
	 * Save a schedule group.
	 *
	 * @return \yii\web\Response|null
	 * @throws \craft\errors\MissingComponentException
	 * @throws \yii\web\BadRequestHttpException
	 * @throws \yii\web\ForbiddenHttpException
	 */
	public function actionSaveScheduleGroup()
	{
		$this->requirePostRequest();
		$this->requireAdmin();

		$scheduleGroup = new ScheduleGroupModel();

		// Set the simple stuff
		$scheduleGroup->id = Craft::$app->getRequest()->getBodyParam('scheduleGroupId');
		$scheduleGroup->name = Craft::$app->getRequest()->getBodyParam('name');
		$scheduleGroup->handle = Craft::$app->getRequest()->getBodyParam('handle');

		// Set the field layout
		$fieldLayout = Craft::$app->getFields()->assembleLayoutFromPost();
		$fieldLayout->type = Schedule::class;

		$scheduleGroup->setFieldLayout($fieldLayout);

		// Save it
		if (!EventsModule::getInstance()->schedule->saveScheduleGroup($scheduleGroup)) {
			Craft::$app->getSession()->setError(Craft::t('app', 'Couldn’t save the schedule group.'));

			// Send the tag group back to the template
			Craft::$app->getUrlManager()->setRouteParams([
				'scheduleGroup' => $scheduleGroup
			]);

			return null;
		}

		Craft::$app->getSession()->setNotice(Craft::t('app', 'Schedule group saved.'));

		return $this->redirectToPostedUrl($scheduleGroup);
	}

	public function actionGroups(): \yii\web\Response
	{
		$this->requireAdmin();

		$scheduleGroups = ScheduleGroupRecord::find()->all();

		return $this->renderTemplate('events/schedule/groups/index', [
			'scheduleGroups' => $scheduleGroups
		]);
	}
}