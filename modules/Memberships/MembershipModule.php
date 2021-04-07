<?php
/**
 * User: sidavies
 * Date: 2019-07-31
 * Time: 15:38
 * File: Login.php
 */

namespace modules\Memberships;


use Craft;
use craft\commerce\elements\db\ProductQuery;
use craft\commerce\elements\Subscription;
use craft\commerce\events\CreateSubscriptionEvent;
use craft\commerce\events\SubscriptionEvent;
use craft\commerce\models\subscriptions\SubscriptionForm;
use craft\commerce\Plugin as Commerce;
use craft\commerce\services\Subscriptions;
use craft\controllers\UsersController;
use craft\elements\Category;
use craft\elements\db\ElementQuery;
use craft\elements\User;
use craft\events\CancelableEvent;
use craft\events\ElementEvent;
use craft\events\ElementQueryEvent;
use craft\events\ModelEvent;
use craft\events\PopulateElementEvent;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterTemplateRootsEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\events\RegisterUserActionsEvent;
use craft\events\RegisterUserPermissionsEvent;
use craft\events\TemplateEvent;
use craft\events\UserAssignGroupEvent;
use craft\events\UserGroupsAssignEvent;
use craft\mail\Message;
use craft\migrations\m171231_055546_environment_variables_to_aliases;
use craft\services\Elements;
use craft\services\Fields;
use craft\services\Routes;
use craft\services\UserPermissions;
use craft\services\Users;
use craft\web\twig\variables\CraftVariable;
use craft\web\UrlManager;
use craft\web\View;
use modules\Memberships\Fields\UserGroups;
use modules\Memberships\Services\Permission;
use modules\Yalumba\Jobs\DeleteMemberJob as YalumbaDeleteMemberJob;
use modules\Vend\Jobs\DeleteMemberJob as VendDeleteMemberJob;
use modules\Yalumba\Jobs\SendCreateMemberJob as YalumbaSendCreateMemberJob;
use modules\Yalumba\Jobs\SendUpdateMemberJob as YalumbaSendUpdateMemberJob;
use modules\Vend\Jobs\SendCreateMemberJob as VendSendCreateMemberJob;
use modules\Vend\Jobs\SendUpdateMemberJob as VendSendUpdateMemberJob;
use modules\Memberships\Services\Members;
use modules\Yalumba\YalumbaApiModule;
use yii\base\Event;
use yii\base\Module;
use yii\web\UserEvent;

class MembershipModule extends Module {

	public function __construct($id, $parent = null, array $config = []) {

		Craft::setAlias('@modules/Memberships', $this->getBasePath());
		$this->controllerNamespace = 'modules\Memberships\Controllers';
		// Set this as the global instance of this module class
		static::setInstance($this);
		parent::__construct($id, $parent, $config);
	}

	public function init(){
		parent::init();

		$this->setComponents([
			'members' => Members::class
        ]);

		Event::on(Fields::class, Fields::EVENT_REGISTER_FIELD_TYPES, function(RegisterComponentTypesEvent $event) {
//			$event->types[] = UserGroups::class; //TODO: this can get removed too - we're using multiselect instead.

//			$event->types[] = SubscriptionShippingAddress::class;   //subscriptions requirements have been removed
//			$event->types[] = SubscriptionPaymentSource::class;     //subscriptions requirements have been removed
			$event->types[] = \modules\Memberships\Fields\Permission::class;     //perm
		});

		Event::on(ElementQuery::class, \craft\db\Query::EVENT_DEFINE_BEHAVIORS,function( \craft\events\DefineBehaviorsEvent $event){
            $behaviours = $event->sender->attachBehaviors([
                  MembershipPermissionBehavior::class,
            ]);
		});

		// Register our CP routes
		Event::on(
			UrlManager::class,
			UrlManager::EVENT_REGISTER_CP_URL_RULES,
			function (RegisterUrlRulesEvent $event) {
				$event->rules = array_merge($event->rules,$this->cpRoutes());
			}
		);

		// Base template directory
		Event::on(View::class, View::EVENT_REGISTER_CP_TEMPLATE_ROOTS, function (RegisterTemplateRootsEvent $e) {
			if (is_dir($baseDir = $this->getBasePath().DIRECTORY_SEPARATOR.'templates')) {
				$e->roots[$this->id] = $baseDir;
			}
		});

		//Add permissions to user groups
		Event::on(
			UserPermissions::class,
			UserPermissions::EVENT_REGISTER_PERMISSIONS,
			function(RegisterUserPermissionsEvent $event) {
				$userGroups = \Craft::$app->userGroups->getAllGroups();
				$permissions = [];
				foreach($userGroups as $userGroup){
					$permissions["view-$userGroup->handle"] = [
						"label"=>"Can view elements onlv visible to $userGroup->name"
					];
				}
				$event->permissions["User Group View"] =  $permissions;
			}
		);

        //Disable the act as user functionality.
        Event::on(
            UsersController::class,
            UsersController::EVENT_REGISTER_USER_ACTIONS,
            function(RegisterUserActionsEvent $e) {
                foreach ($e->sessionActions as $i => $action) {
                    if ($action['action'] === 'users/impersonate') {
                        unset($e->sessionActions[$i]);
                    }
                }
            });

		//REGISTER TEMPLATE PATHS
		Event::on(View::class, View::EVENT_REGISTER_SITE_TEMPLATE_ROOTS, function (RegisterTemplateRootsEvent $e) {
			if (is_dir($baseDir = $this->getBasePath().DIRECTORY_SEPARATOR.'templates')) {
				$e->roots[$this->id] = $baseDir;
			}
		});

		Event::on(User::class, User::EVENT_AFTER_DELETE, function (Event $event){
            //before user is deleted:
            Craft::$app->queue->push(new YalumbaDeleteMemberJob([ 'userId' =>$event->sender->id, 'userUid'=>$event->sender->uid]));
            Craft::$app->queue->push(new VendDeleteMemberJob([ 'userId' =>$event->sender->id, 'userUid'=>$event->sender->uid]));
        });

        Event::on(User::class, User::EVENT_BEFORE_SAVE, function (ModelEvent $event){

            // Chanda 26th Feb 2021 - If the saving is bulk saving then APIs not need to be fired
            if(!$event->isNew && !$event->sender->resaving){
                //we need the old user's member details:
                $currentUser = User::find()->id($event->sender)->one();
                $oldGroupIds = self::getInstance()->members->getUserGroupIds($currentUser);
                Craft::$app->queue->push(new YalumbaSendUpdateMemberJob(['userId' =>$event->sender->id,'userUid'=>$event->sender->uid, 'groupIds' => $oldGroupIds]));
                Craft::$app->queue->push(new VendSendUpdateMemberJob(['userId' =>$event->sender->id, 'userUid'=>$event->sender->uid]));
            }
        });

        // send email if email address has changed.
        Event::on(User::class, User::EVENT_BEFORE_SAVE, function (ModelEvent $event){
            if(!$event->isNew){
                $oldUser = User::findOne($event->sender->id);
                $newUser = $event->sender;

                if($oldUser->email != $newUser->email){
                    $mailer = Craft::$app->getMailer();
                    $view = Craft::$app->getView();
                    $view->setTemplateMode($view::TEMPLATE_MODE_SITE);
                    $renderVariables = compact('oldUser', 'newUser');
                    $templatePath = 'emails/customer/email_changed';
                    $body = $view->renderTemplate($templatePath, $renderVariables);
                    $message = (new Message())
                        ->setFrom($mailer->from)
                        ->setTo($newUser->email)
                        ->setReplyTo($mailer->from)
                        ->setSubject('Email address changed')
                        ->setTextBody("Membership email address changed")
                        ->setHtmlBody($body);
                    $mailer->send($message);

                }
            }
        });

        Event::on(User::class, User::EVENT_AFTER_SAVE, function (ModelEvent $event) {
            if($event->isNew) {
                $params = ['userId' => $event->sender->id, 'userUid'=>$event->sender->uid];
                Craft::$app->queue->push(new YalumbaSendCreateMemberJob($params));
                Craft::$app->queue->push(new VendSendCreateMemberJob($params));
            }
        });



		Event::on(
			Users::class,
			Users::EVENT_BEFORE_SUSPEND_USER,
			function(UserEvent $event){
                // TODO: work out what to do here. I don't think we have to do anything.
                // user will be suspended from using the cms - maybe we should send to
                // vend?
			});
	}

	public function cpRoutes() {
	    return [];
//		return [
//			'/memberships/trigger-payment' => 'memberships/memberships/trigger-payment',
//			'/memberships/trigger-payment/<membershipHandle:{handle}>' => 'memberships/memberships/trigger-payment',
//		];
	}

}