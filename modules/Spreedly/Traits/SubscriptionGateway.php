<?php


namespace modules\Spreedly\Traits;


use craft\commerce\base\Plan as BasePlan;
use craft\commerce\base\SubscriptionResponseInterface;
use craft\commerce\elements\Subscription;
use craft\commerce\errors\SubscriptionException;
use craft\commerce\models\subscriptions\CancelSubscriptionForm;
use craft\commerce\models\subscriptions\SubscriptionForm;
use craft\commerce\models\subscriptions\SubscriptionPayment;
use craft\commerce\models\subscriptions\SwitchPlansForm;
use craft\elements\User;
use craft\helpers\StringHelper;
use modules\Spreedly\Models\Plan;
use modules\Spreedly\SpreedlyModule;
use modules\Spreedly\SubscriptionResponse;

trait SubscriptionGateway {

	/**
	 * Returns the cancel subscription form HTML
	 *
	 * @param Subscription $subscription the subscription to cancel
	 *
	 * @return string
	 */
	public function getCancelSubscriptionFormHtml( Subscription $subscription ): string {
		// TODO: Implement getCancelSubscriptionFormHtml() method.
		return '';
	}

	/**
	 * Returns the cancel subscription form model
	 *
	 * @return CancelSubscriptionForm
	 */
	public function getCancelSubscriptionFormModel(): CancelSubscriptionForm {
		// TODO: Implement getCancelSubscriptionFormModel() method.
		return new CancelSubscriptionForm();
	}

	/**
	 * Returns the subscription plan settings HTML
	 *
	 * @param array $params
	 *
	 * @return string|null
	 */
	public function getPlanSettingsHtml( array $params = [] ) {
		// TODO: Implement getPlanSettingsHtml() method.
		//return 'here';
		return '<input type="hidden" name="reference" value="dummy.reference"/>';
	}

	/**
	 * Returns the subscription plan model.
	 *
	 * @return Plan
	 */
	public function getPlanModel(): BasePlan
	{
		return new Plan();
	}

	/**
	 * Returns the subscription form model
	 *
	 * @return SubscriptionForm
	 */
	public function getSubscriptionFormModel(): SubscriptionForm {
		// TODO: Implement getSubscriptionFormModel() method.
		return new SubscriptionForm();
	}

	/**
	 * Returns the form model used for switching plans.
	 *
	 * @return SwitchPlansForm
	 */
	public function getSwitchPlansFormModel(): SwitchPlansForm {
		// TODO: Implement getSwitchPlansFormModel() method.
		return new SwitchPlansForm();
	}

	/**
	 * Cancels a subscription.
	 *
	 * @param Subscription $subscription the subscription to cancel
	 * @param CancelSubscriptionForm $parameters additional parameters to use
	 *
	 * @return SubscriptionResponseInterface
	 * @throws SubscriptionException for all subscription-related errors.
	 */
	public function cancelSubscription( Subscription $subscription, CancelSubscriptionForm $parameters ): SubscriptionResponseInterface {
		// TODO: Implement cancelSubscription() method.
		$response = new SubscriptionResponse();
		$response->setIsCanceled(true);
		return $response;
	}

	/**
	 * Returns the next payment amount for a subscription, taking into account all discounts.
	 *
	 * @param Subscription $subscription
	 *
	 * @return string next payment amount with currency code
	 */
	public function getNextPaymentAmount( Subscription $subscription ): string {
		//TODO: this should get the subscription plans' info page and get the
		// amount from there.
//		echo('<pre>');
//		var_dump($subscription);
//		exit();
		return '-';
	}

	/**
	 * Returns a list of previous subscription payments for a given subscription.
	 *
	 * @param Subscription $subscription
	 *
	 * @return SubscriptionPayment[]
	 */
	public function getSubscriptionPayments( Subscription $subscription ): array {
		$payments = SpreedlyModule::getInstance()->subscription_payments->getSubscriptionPayments($subscription->id);
		return $payments;
	}

	/**
	 * Returns a subscription plan by its reference
	 *
	 * @param string $reference
	 *
	 * @return string
	 */
	public function getSubscriptionPlanByReference( string $reference ): string {
		return 'spreedly.plan';
	}

	/**
	 * Returns all subscription plans as array containing hashes with `reference` and `name` as keys.
	 *
	 * @return array
	 */
	public function getSubscriptionPlans(): array {
		// TODO: Implement getSubscriptionPlans() method.
		return [];
	}

	/**
	 * Subscribe user to a plan.
	 *
	 * @param User $user
	 * @param BasePlan $plan
	 * @param SubscriptionForm $parameters
	 *
	 * @return SubscriptionResponseInterface
	 */
	public function subscribe( User $user, BasePlan $plan, SubscriptionForm $parameters ): SubscriptionResponseInterface {

		$myparams = new \modules\Spreedly\Models\SubscriptionForm();

		//TODO: this is dodgy - is there another way to handle this?
		$myparams->paymentSource = \Craft::$app->request->getParam('paymentSource');

		$subscriptionResponse = new SubscriptionResponse();
		$subscriptionResponse->setTrialDays((int)$myparams->trialDays);
		// I have nothing to store in the reference - this is because we don't have any api actions to
		// do at this point (thanks Spreedly). So I think I can just stuff something meaningless in
		// here for now.
		// TODO: Maybe I should put the uid in here instead?
		$subscriptionResponse->setReference(StringHelper::randomString());

		return $subscriptionResponse;
	}

	/**
	 * Switch a subscription to a different subscription plan.
	 *
	 * @param Subscription $subscription the subscription to modify
	 * @param BasePlan $plan the plan to change the subscription to
	 * @param SwitchPlansForm $parameters additional parameters to use
	 *
	 * @return SubscriptionResponseInterface
	 */
	public function switchSubscriptionPlan( Subscription $subscription, BasePlan $plan, SwitchPlansForm $parameters ): SubscriptionResponseInterface {
		// TODO: Implement switchSubscriptionPlan() method.
		return new SubscriptionResponse();
	}

	/**
	 * Returns whether this gateway supports reactivating subscriptions.
	 *
	 * @return bool
	 */
	public function supportsReactivation(): bool {
		// TODO: Implement supportsReactivation() method.
		return true;
	}

	/**
	 * Returns whether this gateway supports switching plans.
	 *
	 * @return bool
	 */
	public function supportsPlanSwitch() : bool{
		// TODO: Implement supportsPlanSwitch() method.
		return true;
	}
}