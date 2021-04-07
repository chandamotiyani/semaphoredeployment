<?php

namespace modules\Spreedly;

use craft\commerce\base\RequestResponseInterface;
use craft\commerce\base\SubscriptionGateway;
use craft\commerce\elements\Subscription;
use craft\commerce\models\payments\BasePaymentForm;
use craft\helpers\ArrayHelper;
use craft\helpers\DateTimeHelper;
use modules\Spreedly\Models\Payment;
use modules\Spreedly\Models\PaymentSource;
use craft\commerce\models\Transaction;
use craft\web\Response as WebResponse;
use craft\web\View;
use craft\commerce\Plugin as Commerce;
use modules\Spreedly\Contracts\ManualRecurringPayments;
use modules\Spreedly\Models\PaymentForm;
use modules\Spreedly\Models\SubscriptionPayment;
use modules\Spreedly\Traits\SubscriptionGateway as SubscriptionGatewayTrait;
use modules\Spreedly\Traits\SpreedlyActions;
use Throwable;
use \Craft;

/**
 * Gateway represents eWay Rapid Direct gateway
 *
 * @author    Pixel & Tonic, Inc. <support@pixelandtonic.com>
 * @since     1.0
 */
class Gateway extends SubscriptionGateway implements ManualRecurringPayments {
	use SubscriptionGatewayTrait;
	use SpreedlyActions;

	public $environment_key;
	public $access_secret;
	public $gateway_token;
	public $test_mode_gateway_token;
	public $test_mode;

	/**
	 * @inheritdoc
	 */
	public static function displayName(): string {
		return \Craft::t( 'commerce', 'Spreedly' );
	}

	/**
	 * @inheritdoc
	 */
	public function getSettingsHtml() {
		return Craft::$app->getView()->renderTemplate( 'spreedly/gatewaySettings', [ 'gateway' => $this ] );
	}

	/**
	 * @inheritdoc
	 */
	public function getPaymentFormHtml( array $params ) {

		$defaults = [
			'paymentForm' => $this->getPaymentFormModel(),
		];

		$params = array_merge( $defaults, $params );

		$view         = Craft::$app->getView();
		$previousMode = $view->getTemplateMode();
		$view->setTemplateMode( View::TEMPLATE_MODE_CP );
		$html = Craft::$app->getView()->renderTemplate( 'spreedly/_creditCardFields', $params );
		$view->setTemplateMode( $previousMode );

		return $html;
	}

	/**
	 * Returns payment form model to use in payment forms.
	 *
	 * @return BasePaymentForm
	 */
	public function getPaymentFormModel(): BasePaymentForm {
		return new PaymentForm();
	}

	/**
	 * Makes an authorize request.
	 *
	 * @param Transaction $transaction The authorize transaction
	 * @param BasePaymentForm $form A form filled with payment info
	 *
	 * @return RequestResponseInterface
	 */
	public function authorize( Transaction $transaction, BasePaymentForm $form ): RequestResponseInterface {
		// TODO: Implement authorize() method.
		echo( '<pre>' );
		var_dump( 'auth' );
		exit();
	}

	/**
	 * Makes a capture request.
	 *
	 * @param Transaction $transaction The capture transaction
	 * @param string $reference Reference for the transaction being captured.
	 *
	 * @return RequestResponseInterface
	 */
	public function capture( Transaction $transaction, string $reference ): RequestResponseInterface {
		// TODO: Implement capture() method.
		echo( '<pre>' );
		var_dump( 'capture' );
		exit();
	}

	/**
	 * Complete the authorization for offsite payments.
	 *
	 * @param Transaction $transaction The transaction
	 *
	 * @return RequestResponseInterface
	 */
	public function completeAuthorize( Transaction $transaction ): RequestResponseInterface {
		// TODO: Implement completeAuthorize() method.
	}

	/**
	 * Complete the purchase for offsite payments.
	 *
	 * @param Transaction $transaction The transaction
	 *
	 * @return RequestResponseInterface
	 */
	public function completePurchase( Transaction $transaction ): RequestResponseInterface {
		// TODO: Implement completePurchase() method.
		echo( '<pre>' );
		var_dump( 'hello world' );
		exit();
	}

	/**
	 * Creates a payment source from source data and user id.
	 *
	 * @param BasePaymentForm $sourceData
	 * @param int $userId
	 *
	 * @return PaymentSource
	 */
	public function createPaymentSource( BasePaymentForm $sourceData, int $userId ): \craft\commerce\models\PaymentSource {

		$user                     = Craft::$app->getUsers()->getUserById( $userId );
		$paymentSource            = new PaymentSource();
		$paymentSource->gatewayId = $this->id;
		$paymentSource->userId    = $user->id;

		$response                 = $this->addSpreedlyCard( $sourceData, $user );

		if($response->isSuccessful()){
            $paymentSource->token               = $response->getData()->transaction->payment_method->token;
            $paymentSource->description         = $response->getData()->transaction->payment_method->number;
            $paymentSource->useForMemberPayment = $sourceData->useForMemberPayment ? true : false;
            $paymentSource->response = json_encode( $response->getData() );
        }
        return $paymentSource;
	}

	/**
	 * Deletes a payment source on the gateway by its token.
	 *
	 * @param string $token
	 *
	 * @return bool
	 */
	public function deletePaymentSource( $token ): bool {
		// TODO: Implement deletePaymentSource() method.
		$response = $this->deleteSpreedlyCard( $token );
		if ( $response->isSuccessful() ) {
			return true;
		} else {
			if ( $response->getCode() == 404 ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Makes a purchase request.
	 *
	 * @param Transaction $transaction The purchase transaction
	 * @param BasePaymentForm $form A form filled with payment info
	 *
	 * @return RequestResponseInterface
	 */
	public function purchase( Transaction $transaction, BasePaymentForm $form ): RequestResponseInterface {

		if($form->cardReference){
			//do we have a token?
			//if so we can use this to load up a paymentSource..
			$paymentSources = Commerce::getInstance()->getPaymentSources()->getAllPaymentSourcesByUserId($transaction->userId);
			$paymentSource = ArrayHelper::firstWhere($paymentSources, 'token', $form->cardReference);

		}
		else{
			if($transaction->userId){
				//no user means we don't want to save the cc
				$paymentSource = $this->createPaymentSource($form, $transaction->userId);
			}
			else{
				$paymentSource = $form;
			}
		}

		$payment = new Payment();
		$payment->gateway = $transaction->gateway;
		$payment->paymentCurrency = $transaction->paymentCurrency;
		$payment->paymentAmount = $transaction->paymentAmount;
		$payment->order = $transaction->order;
		$payment->email = $transaction->order->email;
		$payment->nextPaymentDateTime = NULL;
        $payment->shippingAddress = $transaction->order->shippingAddress;
        $payment->billingAddress = $transaction->order->billingAddress;

		$response = $this->makeSpreedlyCardPayment( $payment, $paymentSource );
		return $response;
	}

	/**
	 * Makes an refund request.
	 *
	 * @param Transaction $transaction The refund transaction
	 *
	 * @return RequestResponseInterface
	 */
	public function refund( Transaction $transaction ): RequestResponseInterface {
		$response = $this->makeSpreedlyRefundPayment( $transaction );

		return $response;
	}

	/**
	 * Processes a webhook and return a response
	 *
	 * @return WebResponse
	 * @throws Throwable if something goes wrong
	 */
	public function processWebHook(): WebResponse {
		// TODO: Implement processWebHook() method.
	}

	/**
	 * Returns true if gateway supports authorize requests.
	 *
	 * @return bool
	 */
	public function supportsAuthorize(): bool {
		// TODO: Implement supportsAuthorize() method.
	}

	/**
	 * Returns true if gateway supports capture requests.
	 *
	 * @return bool
	 */
	public function supportsCapture(): bool {
		// TODO: Implement supportsCapture() method.
		return false;
	}

	/**
	 * Returns true if gateway supports completing authorize requests
	 *
	 * @return bool
	 */
	public function supportsCompleteAuthorize(): bool {
		// TODO: Implement supportsCompleteAuthorize() method.
		return false;
	}

	/**
	 * Returns true if gateway supports completing purchase requests
	 *
	 * @return bool
	 */
	public function supportsCompletePurchase(): bool {
		// TODO: Implement supportsCompletePurchase() method.
		return false;
	}

	/**
	 * Returns true if gateway supports payment sources
	 *
	 * @return bool
	 */
	public function supportsPaymentSources(): bool {
		return true;
	}

	/**
	 * Returns true if gateway supports purchase requests.
	 *
	 * @return bool
	 */
	public function supportsPurchase(): bool {
		return true;
	}

	/**
	 * Returns true if gateway supports refund requests.
	 *
	 * @return bool
	 */
	public function supportsRefund(): bool {
		return true;
	}

	/**
	 * Returns true if gateway supports partial refund requests.
	 *
	 * @return bool
	 */
	public function supportsPartialRefund(): bool {
		return true;
	}

	/**
	 * Returns true if gateway supports webhooks.
	 *
	 * @return bool
	 */
	public function supportsWebhooks(): bool {
		return false;
	}

	public function makeRecurringPayment( \craft\commerce\models\PaymentSource $paymentSource, Payment $payment ) {
		$response = $this->makeSpreedlyCardPayment($paymentSource, $payment);

		$subscriptionPayment                   = new SubscriptionPayment();
		$subscriptionPayment->subscriptionId   = $payment->order->subscription->id;
		$subscriptionPayment->paymentAmount    = $payment->paymentAmount;
		$subscriptionPayment->paymentCurrency  = $payment->paymentCurrency;
		$subscriptionPayment->paymentDate      = DateTimeHelper::toDateTime( time() );
		$subscriptionPayment->paymentReference = $response->getReference();
		$subscriptionPayment->paid             = $response->isSuccessful();
		$subscriptionPayment->response         = json_encode($response->getData());
		$subscriptionPayment = SpreedlyModule::getInstance()->subscription_payments->create( $subscriptionPayment );
		$subscription = Commerce::getInstance()->getSubscriptions()->receivePayment($payment->order->subscription,$subscriptionPayment, $payment->nextPaymentDateTime );

		return $response;
	}

    /**
     * @inheritDoc
     */
    public function getHasBillingIssues(Subscription $subscription): bool
    {
        return false; // TODO: Implement getHasBillingIssues() method.
    }

    /**
     * @inheritDoc
     */
    public function getBillingIssueDescription(Subscription $subscription): string
    {
        return '';// TODO: Implement getBillingIssueDescription() method.
    }

    /**
     * @inheritDoc
     */
    public function getBillingIssueResolveFormHtml(Subscription $subscription): string
    {
       return ''; // TODO: Implement getBillingIssueResolveFormHtml() method.
    }
}
