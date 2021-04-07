<?php


namespace modules\Spreedly\Traits;


use craft\commerce\base\Gateway;
use craft\commerce\models\payments\BasePaymentForm;
use craft\commerce\models\PaymentSource;
use craft\commerce\models\Transaction;
use craft\commerce\stripe\errors\CustomerException;
use modules\Spreedly\Models\Payment;
use modules\Spreedly\Responses\SpreedlyResponse;
use modules\Spreedly\Responses\SpreedlySaveCardResponse;
use modules\Spreedly\SpreedlyModule;

trait SpreedlyActions {

	private function addSpreedlyCard(BasePaymentForm $card, $user){
		try {
			$response = SpreedlyModule::getInstance()->spreedly_api->addCard($card, $user, $this);
			$response = new SpreedlySaveCardResponse($response);
			return $response;
		}
		catch(\Exception $e){
			//TODO: error message in here.
			echo('<pre>');
			var_dump('addcard');
			var_dump($e);
			exit();
			//throw new CustomerException('Could not fetch Stripe customer: ' . $e->getMessage());
		}
	}

	private function deleteSpreedlyCard($token){
		try {
			$response = SpreedlyModule::getInstance()->spreedly_api->deleteCard($token, $this);

			$response = new SpreedlyResponse($response);

			return $response;
		}
		catch(\Exception $e){
			//TODO: error message in here.
			echo('<pre>');
			var_dump($e);
			exit();
		}
	}

	private function makeSpreedlyCardPayment($payment, $paymentSource){
		try {
			$response = SpreedlyModule::getInstance()->spreedly_api->makePayment($payment, $paymentSource);

			$response = new SpreedlyResponse($response);

			return $response;
		}
		catch(\Exception $e){
			//TODO: error message in here.
			echo('<pre>');
			var_dump($e->getMessage());
			var_dump('jdas');
			exit();
			echo('<pre>');
			var_dump($e->getMessage());
			exit();
			//throw new CustomerException('Could not fetch Stripe customer: ' . $e->getMessage());
		}
	}

	private function makeSpreedlyRefundPayment(Transaction $transaction){
		try {
			$response = SpreedlyModule::getInstance()->spreedly_api->makeRefundPayment($transaction);
			$response = new SpreedlyResponse($response);

			return $response;
		}
		catch(\Exception $e){
			//TODO: error message in here.
			echo('<pre>');
			var_dump($e->getMessage());
			exit();
			//throw new CustomerException('Could not fetch Stripe customer: ' . $e->getMessage());
		}
	}

	private function makeSpreedlyRecurringPayment($transaction, $form){
		try {
			$response = SpreedlyModule::getInstance()->spreedly_api->makeRecurringPayment($transaction, $form);

			$response = new SpreedlyResponse($response);

			return $response;
		}
		catch(\Exception $e){
			//TODO: error message in here.
			echo('<pre>');
			var_dump('jdas');
			exit();
			echo('<pre>');
			var_dump($e->getMessage());
			exit();
			//throw new CustomerException('Could not fetch Stripe customer: ' . $e->getMessage());
		}
	}

}