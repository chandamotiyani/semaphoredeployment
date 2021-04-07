<?php
/**
 * 
 */

namespace modules\FreeShipping;

use Craft;
use craft\base\Component;
use craft\commerce\base\AdjusterInterface;
use craft\commerce\elements\Order;
use craft\commerce\models\OrderAdjustment;
use craft\commerce\adjusters\Shipping as ShippingAdjuster;

class FreeShippingAdjuster extends Component implements AdjusterInterface {

	public function adjust(Order $order): array {

		$adjustments = [];
		
		$shippingMethod = $order->getShippingMethod();
		if($shippingMethod){

			$shippingRules = $shippingMethod->getShippingRules();
			/*$currentUser = Craft::$app->getUser();
			$membershipLevel = '';

			if(!empty($currentUser) && $currentUser->getIdentity()->getGroups()) {
				$group = $currentUser->getIdentity()->getGroups()[0];
				error_log(print_r($group,true));
				$membershipLevel = $group->handle;
			}*/

			$freeShipping = false;

			foreach ($order->getLineItems() as $item) {
				
				if($item->purchasable->product && $item->purchasable->product->freeShipping){
					//Product has free shipping available
					$freeShipping = true;
				}
			}

			if($freeShipping){
				//Product has free shipping available
				$adjustment = new OrderAdjustment();
				$adjustment->type = 'shipping';
				$adjustment->setOrder($order);
				$adjustment->name = "Free Shipping";
				$adjustment->amount = 0;
				$adjustments[] = $adjustment;
			}else{
				//Use Flat rate shipping
				if($shippingRules){
					$shippingAdjustment = new ShippingAdjuster();

					return $shippingAdjustment->adjust($order);
				}
			}
		}

		return $adjustments;
	}
}