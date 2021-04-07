<?php

namespace modules\Spreedly;

use craft\commerce\events\PaymentSourceEvent;
use craft\commerce\records\PaymentSource;
use craft\commerce\services\Gateways as CommerceGateways;
use craft\commerce\services\PaymentSources;
use craft\events\RegisterComponentTypesEvent;
use Craft;
use craft\events\RegisterTemplateRootsEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\services\Fields;
use craft\web\UrlManager;
use modules\Spreedly\Fields\SubscriptionOrders;
use modules\Spreedly\Services\SpreedlyApi;
use modules\Spreedly\Services\SubscriptionPayments;
use yii\base\Event;
use craft\web\View;
use yii\base\Module;


class SpreedlyModule extends Module {

	public static $instance;

	/**
	 * Spreedly constructor.
	 */
	public function __construct($id, $parent = null, array $config = []){
		Craft::setAlias('@modules/Spreedly', $this->getBasePath());
		$this->controllerNamespace = 'modules\Spreedly\Controllers';

		parent::__construct($id, $parent, $config);
	}


	public function init() {
		parent::init();

		$this->setComponents([
			'spreedly_api' => SpreedlyApi::class,
			'subscription_payments' => SubscriptionPayments::class,
			'subscription_orders' => \modules\Spreedly\Services\SubscriptionOrders::class
		]);

		Event::on(CommerceGateways::class, CommerceGateways::EVENT_REGISTER_GATEWAY_TYPES,  function(RegisterComponentTypesEvent $event) {
			$event->types[] = Gateway::class;
		});

		Event::on(View::class, View::EVENT_REGISTER_CP_TEMPLATE_ROOTS, function (RegisterTemplateRootsEvent $e) {
			if (is_dir($baseDir = $this->getBasePath().DIRECTORY_SEPARATOR.'templates')) {
				$e->roots[$this->id] = $baseDir;
			}
		});

		// Register our CP routes
		Event::on(
			UrlManager::class,
			UrlManager::EVENT_REGISTER_CP_URL_RULES,
			function (RegisterUrlRulesEvent $event) {
				$event->rules = array_merge($event->rules,$this->cpRoutes());
			}
		);

		//register the site template root.
		Event::on(View::class, View::EVENT_REGISTER_SITE_TEMPLATE_ROOTS, function (RegisterTemplateRootsEvent $e) {
			if (is_dir($baseDir = $this->getBasePath().DIRECTORY_SEPARATOR.'templates')) {
				$e->roots[$this->id] = $baseDir;
			}
		});

		Event::on(Fields::class, Fields::EVENT_REGISTER_FIELD_TYPES, function(RegisterComponentTypesEvent $event) {
			$event->types[] = SubscriptionOrders::class;
		});

		// users subscriptions need to be paid using a specific payment source.
		// here we are intercepting a new payment source creation and adding the ability to set a
		// useForMemberPayment flag. A Payment source with this flag set will be used for the
		// subscription payment.
		// If we can work out the UI, This could be modified to allow for different sources for
		// different subscriptions.
		Event::on(PaymentSources::class, PaymentSources::EVENT_AFTER_SAVE_PAYMENT_SOURCE, function(PaymentSourceEvent $e) {

			$paymentSource = $e->paymentSource;
			if($paymentSource->useForMemberPayment){
				$user = Craft::$app->users->getUserById($paymentSource->userId);
				$user->subscriptionPaymentSource = $paymentSource->id;
				$user->setFieldValue('subscriptionPaymentSource', $paymentSource->id);

				//TODO: what validation issue are we hitting to make us need the "false" in here?
				Craft::$app->getElements()->saveElement($user, false);
			}
//				$model = PaymentSource::findOne($paymentSource->id);
//
//				//we need to make sure there's only ever 1 member payment source.. so we unset the others here.
//				$currentUserMemberPaymentSources = PaymentSource::find()->where(['userId'=>$model->userId])
//														->where(['useForMemberPayment'=>true])
//														->all();
//				foreach($currentUserMemberPaymentSources as $currentUserMemberPaymentSource){
//					$currentUserMemberPaymentSource->useForMemberPayment = false;
//					$currentUserMemberPaymentSource->save();
//				}
//
//				$model->useForMemberPayment = true;
//				return $model->save();
//			}
		});

	}

	public function cpRoutes() {
		return [
			'/spreedly/create-test-gateway' => 'spreedly/spreedly/create-test',
			'/spreedly/supported-gateways' => 'spreedly/spreedly/supported-gateways',
		];
	}
}