<?php
/**
 * User: sidavies
 * Date: 2019-07-31
 * Time: 15:38
 * File: Login.php
 */

namespace modules\Events;


use Craft;

use craft\console\controllers\ResaveController;
use craft\events\DefineConsoleActionsEvent;
use craft\events\ElementEvent;
use craft\console\Controller as ConsoleController;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterTemplateRootsEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\services\Elements;
use craft\services\Fields;
use craft\web\twig\variables\Cp;
use craft\web\twig\variables\CraftVariable;
use craft\web\UrlManager;
use craft\web\View;
use modules\Events\Fields\Events;
use modules\Events\Fields\Schedule;
use modules\Events\Services\EventService;
use modules\Events\Services\PurchaseService;
use modules\Events\Services\ScheduleService;
use modules\Importer\Importers\EventScheduleImporter;
use yii\base\Event;
use modules\Events\Elements\Event as EventElement;
use modules\Events\Elements\Schedule as ScheduleElement;


class EventsModule extends \yii\base\Module {

	public static $instance;

	public function __construct($id, $parent = null, array $config = []) {

		Craft::setAlias('@modules/Events', $this->getBasePath());
		$this->controllerNamespace = 'modules\Events\Controllers';

		// Set this as the global instance of this module class
		static::setInstance($this);
		parent::__construct($id, $parent, $config);
	}

	public function init(){
		parent::init();

		$this->setComponents([
			'events' => EventService::class,
			'schedule' => ScheduleService::class,
			'purchase'=>PurchaseService::class
		]);

		//register some events that can happen
		Craft::$app->projectConfig
			->onAdd('scheduleGroups.{uid}', [self::getInstance()->schedule, 'handleChangedScheduleGroup'])
			->onUpdate('scheduleGroups.{uid}', [self::getInstance()->schedule, 'handleChangedScheduleGroup'])
			->onRemove('scheduleGroups.{uid}', [self::getInstance()->schedule, 'handleDeletedScheduleGroup'])

			->onAdd('eventGroups.{uid}', [self::getInstance()->events, 'handleChangedEventGroup'])
			->onUpdate('eventGroups.{uid}', [self::getInstance()->events, 'handleChangedEventGroup'])
			->onRemove('eventGroups.{uid}', [self::getInstance()->events, 'handleDeletedEventGroup']);

		// Register our elements
		Event::on(
			Elements::class,
			Elements::EVENT_REGISTER_ELEMENT_TYPES,
			function (RegisterComponentTypesEvent $event) {
				$event->types[] = EventElement::class;
				$event->types[] = ScheduleElement::class;
			}
		);

//		Event::on(CraftVariable::class, CraftVariable::EVENT_INIT, function(Event $event) {
//			$event->sender->set('events', EventsModule::class);
//		});

		// Base template directory
		Event::on(View::class, View::EVENT_REGISTER_CP_TEMPLATE_ROOTS, function (RegisterTemplateRootsEvent $e) {
			if (is_dir($baseDir = $this->getBasePath().DIRECTORY_SEPARATOR.'templates')) {
				$e->roots[$this->id] = $baseDir;
			}
		});

		//REGISTER TEMPLATE PATHS
		Event::on(View::class, View::EVENT_REGISTER_SITE_TEMPLATE_ROOTS, function (RegisterTemplateRootsEvent $e) {
			if (is_dir($baseDir = $this->getBasePath().DIRECTORY_SEPARATOR.'templates')) {
				$e->roots[$this->id] = $baseDir;
			}
		});

		//REGISTER NAV ITEM
		Event::on(
			Cp::class,
			Cp::EVENT_REGISTER_CP_NAV_ITEMS,
			function(craft\events\RegisterCpNavItemsEvent $event) {
				$event->navItems[] = [
					'url' => 'events',
					'label' => 'Events',
					'subnav' => [
						'events' => ['label' => 'Events List', 'url' => 'events'],
						'events-groups' => ['label' => 'Event Groups', 'url' => 'events/groups'],
					],
				];
			}
		);

		// add a couple of custom fields
		Event::on(Fields::class, Fields::EVENT_REGISTER_FIELD_TYPES, function(RegisterComponentTypesEvent $event) {
			$event->types[] = Events::class;
			$event->types[] = Schedule::class;
		});

		// Register our site routes
		Event::on(
			UrlManager::class,
			UrlManager::EVENT_REGISTER_SITE_URL_RULES,
			function (RegisterUrlRulesEvent $event) {
				$event->rules = array_merge($event->rules,$this->siteRoutes());
			}
		);

		// Register our CP routes
		Event::on(
			UrlManager::class,
			UrlManager::EVENT_REGISTER_CP_URL_RULES,
			function (RegisterUrlRulesEvent $event) {
				$event->rules = array_merge($event->rules,$this->cpRoutes());
			}
		);

        //Attach the behaviours
		Event::on(CraftVariable::class, CraftVariable::EVENT_INIT, function(Event $e) {
			/** @var CraftVariable $variable */
			$behaviours = $e->sender->attachBehaviors([
				EventVariableBehavior::class,
			]);
		});

		Event::on(CraftVariable::class, CraftVariable::EVENT_INIT, function(Event $e) {
			/** @var CraftVariable $variable */
			$variable = $e->sender;
			// Attach a service:
			$variable->set('schedules', ScheduleService::class);
		});

        Event::on(ResaveController::class, ConsoleController::EVENT_DEFINE_ACTIONS, function(DefineConsoleActionsEvent $e) {
            $e->actions['events'] = [
                'action' => function(): int {
                    /** @var ResaveController $controller */
                    $controller = Craft::$app->controller;
                    $query = \modules\Events\Elements\Event::find();
                    if ($controller->type !== null) {
                        $query->type(explode(',', $controller->type));
                    }
                    return $controller->saveElements($query);
                },
                'options' => ['type'],
                'helpSummary' => 'Re-saves events.',
                'optionsHelp' => [
                    'type' => 'The event type handle(s) of the products to resave.',
                ],
            ];
        });

	}


	public function siteRoutes(){
		return [
			'/calendar/events/<eventGroupHandle:{handle}>'=>'events/calendar/event-group-events',
			'/calendar/schedule/<eventGroupHandle:{handle}>'=>'events/calendar/event-group-schedule',
			'/calendar/filter/<eventGroupHandle:{handle}>'=>'events/calendar/event-filter',
			'/calendar/search'=>'events/calendar/search',
			'/calendar/filter'=>'events/calendar/event-filter'
		];
	}


	/**
	 * Define CP routes
	 * @return array
	 */
	public function cpRoutes() {
		return [
			'/events/schedule' => 'events/schedule/index',
			'/events/schedule/<id:\d+>/edit' => 'events/schedule/edit',
			'/events/schedule/groups/new' => 'events/schedule/edit-schedule-group',
			'/events/schedule/groups/<scheduleGroupId:\d+>/edit' => 'events/schedule/edit-schedule-group',
			'/events/schedule/groups/' => 'events/schedule/groups',
			'/events/schedule/save' => 'events/schedule/save-schedule-group',


			'/events/groups' => 'events/events/groups',
			'/events/groups/new' => 'events/events/edit-event-group',
			'/events/groups/save' => 'events/events/save-event-group',
			'/events/groups/delete' => 'events/events/delete-event-group',
			'/events/groups/<eventGroupId:\d+>' => 'events/events/edit-event-group',

			'/events' => 'events/events/index',
			'/events/<eventGroupHandle:{handle}>' => 'events/events/index',
			'/events/<eventGroupHandle:{handle}>/new' => 'events/events/new',
			'/events/<id:\d+>/edit' => 'events/events/edit',
			'/events/<id:\d+>/delete' => 'events/events/delete',
			'/events/field-layout' => 'events/events/field-layout',
		];
	}
}