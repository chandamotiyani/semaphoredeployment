<?php
namespace verbb\wishlist;

use verbb\wishlist\base\PluginTrait;
use verbb\wishlist\elements\ListElement;
use verbb\wishlist\elements\Item;
use verbb\wishlist\fieldlayoutelements\OptionsField;
use verbb\wishlist\gql\interfaces\ListInterface;
use verbb\wishlist\gql\interfaces\ItemInterface;
use verbb\wishlist\gql\queries\ListQuery;
use verbb\wishlist\gql\queries\ItemQuery;
use verbb\wishlist\helpers\ProjectConfigData;
use verbb\wishlist\models\Settings;
use verbb\wishlist\services\ListTypes;
use verbb\wishlist\variables\WishlistVariable;

use Craft;
use craft\base\Plugin;
use craft\console\controllers\ResaveController;
use craft\console\Controller as ConsoleController;
use craft\elements\User as UserElement;
use craft\events\DefineConsoleActionsEvent;
use craft\events\DefineFieldLayoutFieldsEvent;
use craft\events\RebuildConfigEvent;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterEmailMessagesEvent;
use craft\events\RegisterGqlQueriesEvent;
use craft\events\RegisterGqlSchemaComponentsEvent;
use craft\events\RegisterGqlTypesEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\events\RegisterUserPermissionsEvent;
use craft\helpers\UrlHelper;
use craft\models\FieldLayout;
use craft\services\Elements;
use craft\services\Fields;
use craft\services\Gc;
use craft\services\Gql;
use craft\services\ProjectConfig;
use craft\services\SystemMessages;
use craft\services\UserPermissions;
use craft\web\UrlManager;
use craft\web\twig\variables\CraftVariable;

use yii\base\Event;
use yii\web\User;

class Wishlist extends Plugin
{
    // Public Properties
    // =========================================================================

    public $schemaVersion = '1.0.4';
    public $hasCpSettings = true;
    public $hasCpSection = true;


    // Traits
    // =========================================================================

    use PluginTrait;


    // Public Methods
    // =========================================================================

    public function init()
    {
        parent::init();

        self::$plugin = $this;

        $this->_setPluginComponents();
        $this->_setLogging();
        $this->_registerCpRoutes();
        $this->_registerPermissions();
        $this->_registerSessionEventListeners();
        $this->_registerEmailMessages();
        $this->_registerVariables();
        $this->_registerElementTypes();
        $this->_registerProjectConfigEventListeners();
        $this->_registerGarbageCollection();
        $this->_registerTemplateHooks();
        $this->_defineResaveCommand();
        $this->_defineFieldLayoutElements();
        $this->_registerGraphQl();
    }

    public function getPluginName()
    {
        return Craft::t('wishlist', $this->getSettings()->pluginName);
    }

    public function getSettingsResponse()
    {
        Craft::$app->getResponse()->redirect(UrlHelper::cpUrl('wishlist/settings'));
    }

    public function getCpNavItem(): array
    {
        $navItems = parent::getCpNavItem();

        $navItems['label'] = $this->getPluginName();

        if (Craft::$app->getUser()->checkPermission('wishlist-manageLists')) {
            $navItems['subnav']['lists'] = [
                'label' => Craft::t('wishlist', 'Lists'),
                'url' => 'wishlist/lists',
            ];
        }

        if (Craft::$app->getUser()->checkPermission('wishlist-manageListTypes')) {
            $navItems['subnav']['listTypes'] = [
                'label' => Craft::t('wishlist', 'List Types'),
                'url' => 'wishlist/list-types',
            ];
        }

        if (Craft::$app->getUser()->getIsAdmin() && Craft::$app->getConfig()->getGeneral()->allowAdminChanges) {
            $navItems['subnav']['settings'] = [
                'label' => Craft::t('wishlist', 'Settings'),
                'url' => 'wishlist/settings',
            ];
        }

        return $navItems;
    }


    // Protected Methods
    // =========================================================================

    protected function createSettingsModel(): Settings
    {
        return new Settings();
    }


    // Private Methods
    // =========================================================================

    private function _registerCpRoutes()
    {
        Event::on(UrlManager::class, UrlManager::EVENT_REGISTER_CP_URL_RULES, function(RegisterUrlRulesEvent $event) {
            $event->rules = array_merge($event->rules, [
                'wishlist' => 'wishlist/lists/index',
                
                'wishlist/lists/<listTypeHandle:{handle}>' => 'wishlist/lists/index',
                'wishlist/lists/<listTypeHandle:{handle}>/new' => 'wishlist/lists/edit-list',
                'wishlist/lists/<listTypeHandle:{handle}>/<listId:\d+>' => 'wishlist/lists/edit-list',
                'wishlist/lists/<listTypeHandle:{handle}>/<listId:\d+>/items/new' => 'wishlist/items/edit-item',
                'wishlist/lists/<listTypeHandle:{handle}>/<listId:\d+>/items/<itemId:\d+>' => 'wishlist/items/edit-item',
                
                'wishlist/list-types' => 'wishlist/list-types/list-type-index',
                'wishlist/list-types/<listTypeId:\d+>' => 'wishlist/list-types/edit-list-type',
                'wishlist/list-types/new' => 'wishlist/list-types/edit-list-type',
                
                'wishlist/settings' => 'wishlist/settings/index',
                'wishlist/settings/general' => 'wishlist/settings/index',
            ]);
        });
    }

    private function _registerEmailMessages()
    {
        Event::on(SystemMessages::class, SystemMessages::EVENT_REGISTER_MESSAGES, function(RegisterEmailMessagesEvent $event) {
            $event->messages = array_merge($event->messages, [
                [
                    'key' => 'wishlist_share_list',
                    'heading' => Craft::t('wishlist', 'wishlist_share_list_heading'),
                    'subject' => Craft::t('wishlist', 'wishlist_share_list_subject'),
                    'body' => Craft::t('wishlist', 'wishlist_share_list_body'),
                ]
            ]);
        });
    }

    private function _registerPermissions()
    {
        Event::on(UserPermissions::class, UserPermissions::EVENT_REGISTER_PERMISSIONS, function(RegisterUserPermissionsEvent $event) {
            $listTypes = Wishlist::getInstance()->getListTypes()->getAllListTypes();

            $listTypePermissions = [];
            foreach ($listTypes as $id => $listType) {
                $suffix = ':' . $id;
                $listTypePermissions['wishlist-manageListType' . $suffix] = ['label' => Craft::t('wishlist', 'Manage “{type}” lists', ['type' => $listType->name])];
            }

            $event->permissions[Craft::t('wishlist', 'Wishlist')] = [
                'wishlist-manageListTypes' => ['label' => Craft::t('wishlist', 'Manage list types')],
                'wishlist-manageLists' => ['label' => Craft::t('wishlist', 'Manage lists'), 'nested' => $listTypePermissions],
            ];
        });
    }

    private function _registerVariables()
    {
        Event::on(CraftVariable::class, CraftVariable::EVENT_INIT, function(Event $event) {
            $event->sender->set('wishlist', WishlistVariable::class);
        });
    }

    private function _registerElementTypes()
    {
        Event::on(Elements::class, Elements::EVENT_REGISTER_ELEMENT_TYPES, function(RegisterComponentTypesEvent $event) {
            $event->types[] = ListElement::class;
            $event->types[] = Item::class;
        });
    }

    private function _registerProjectConfigEventListeners()
    {
        $projectConfigService = Craft::$app->getProjectConfig();

        $listTypeService = $this->getListTypes();
        $projectConfigService->onAdd(ListTypes::CONFIG_LISTTYPES_KEY . '.{uid}', [$listTypeService, 'handleChangedListType'])
            ->onUpdate(ListTypes::CONFIG_LISTTYPES_KEY . '.{uid}', [$listTypeService, 'handleChangedListType'])
            ->onRemove(ListTypes::CONFIG_LISTTYPES_KEY . '.{uid}', [$listTypeService, 'handleDeletedListType']);

        Event::on(Fields::class, Fields::EVENT_AFTER_DELETE_FIELD, [$listTypeService, 'pruneDeletedField']);

        Event::on(ProjectConfig::class, ProjectConfig::EVENT_REBUILD, function (RebuildConfigEvent $event) {
            $event->config['wishlist'] = ProjectConfigData::rebuildProjectConfig();
        });
    }

    private function _registerGarbageCollection()
    {
        Event::on(Gc::class, Gc::EVENT_RUN, function() {
            // Deletes lists that meet the purge settings
            Wishlist::$plugin->getLists()->purgeInactiveLists();
        });
    }

    private function _registerSessionEventListeners()
    {
        if (!Craft::$app->getRequest()->getIsConsoleRequest()) {
            Event::on(User::class, User::EVENT_AFTER_LOGIN, [$this->getLists(), 'loginHandler']);
        }
    }

    private function _defineResaveCommand()
    {
        if (!Craft::$app->getRequest()->getIsConsoleRequest()) {
            return;
        }

        Event::on(ResaveController::class, ConsoleController::EVENT_DEFINE_ACTIONS, function(DefineConsoleActionsEvent $e) {
            $e->actions['wishlist-items'] = [
                'action' => function(): int {
                    $controller = Craft::$app->controller;

                    $query = Item::find();

                    if ($controller->listId !== null) {
                        $query->listId(explode(',', $controller->listId));
                    }

                    return $controller->saveElements($query);
                },
                'options' => ['listId'],
                'helpSummary' => 'Re-saves Wishlist items.',
                'optionsHelp' => [
                    'type' => 'The list type ID of the items to resave.',
                ],
            ];

            $e->actions['wishlist-lists'] = [
                'action' => function(): int {
                    $controller = Craft::$app->controller;

                    $query = ListElement::find();

                    return $controller->saveElements($query);
                },
                'helpSummary' => 'Re-saves Wishlist lists.',
            ];
        });
    }

    private function _registerGraphQl()
    {
        Event::on(Gql::class, Gql::EVENT_REGISTER_GQL_TYPES, function(RegisterGqlTypesEvent $event) {
            $event->types[] = ListInterface::class;
            $event->types[] = ItemInterface::class;
        });

        Event::on(Gql::class, Gql::EVENT_REGISTER_GQL_QUERIES, function(RegisterGqlQueriesEvent $event) {
            foreach (ListQuery::getQueries() as $key => $value) {
                $event->queries[$key] = $value;
            }

            foreach (ItemQuery::getQueries() as $key => $value) {
                $event->queries[$key] = $value;
            }
        });

        Event::on(Gql::class, Gql::EVENT_REGISTER_GQL_SCHEMA_COMPONENTS, function(RegisterGqlSchemaComponentsEvent $event) {
            $listTypes = Wishlist::getInstance()->getListTypes()->getAllListTypes();

            if (!empty($listTypes)) {
                $label = Craft::t('wishlist', 'Wishlist');
                $event->queries[$label]['wishlistListTypes.all:read'] = ['label' => Craft::t('wishlist', 'View all wishlists')];

                foreach ($listTypes as $listType) {
                    $suffix = 'wishlistListTypes.' . $listType->uid;
                    
                    $event->queries[$label][$suffix . ':read'] = [
                        'label' => Craft::t('wishlist', 'View wishlist type - {listType}', ['listType' => Craft::t('site', $listType->name)]),
                    ];
                }
            }
        });
    }

    private function _registerTemplateHooks()
    {
        if ($this->getSettings()->showListInfoTab) {
            Craft::$app->getView()->hook('cp.users.edit', [$this->getLists(), 'addEditUserListInfoTab']);
            Craft::$app->getView()->hook('cp.users.edit.content', [$this->getLists(), 'addEditUserListInfoTabContent']);
        }
    }

    private function _defineFieldLayoutElements()
    {
        Event::on(FieldLayout::class, FieldLayout::EVENT_DEFINE_STANDARD_FIELDS, function(DefineFieldLayoutFieldsEvent $e) {
            $fieldLayout = $e->sender;

            switch ($fieldLayout->type) {
                case Item::class:
                    $e->fields[] = OptionsField::class;
                    break;
            }
        });
    }

}
