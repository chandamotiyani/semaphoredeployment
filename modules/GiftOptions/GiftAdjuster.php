<?php
/**
 * Gift options are setup as craft commerce products and are sent here from the 
 * 'update-cart' action (from gift option submit form). 
 * We're sending the options data as key/value: 'the_product_id' => 'The Product title'
 * this is so that option key is unique and we don't need to do another product query 
 * when displaying the title on the front end.
 */

namespace modules\GiftOptions;

use craft\base\Component;
use craft\commerce\base\AdjusterInterface;
use craft\commerce\elements\Order;
use craft\commerce\models\OrderAdjustment;

class GiftAdjuster extends Component implements AdjusterInterface {

	public function adjust(Order $order): array {

		$adjustments = [];
		foreach ($order->getLineItems() as $item) {

			/**
			 * Options contain the Gift Items Product ID.
			 * We do a lookup on the Product ID, find the price,
			 * and add it to the cart total.
			 */
			if(!@$item->options) {
				continue;
			}

			foreach($item->options as $productID => $productTitle) {
				if(! is_numeric($productID) ) {
					continue;
				}

				$giftOption = \craft\commerce\elements\Product::find()
				->id([$productID])
				->one();

				if(! isset($giftOption->defaultPrice) ) { // Check we were able to get a product
					continue;
				}

				if($productTitle == '') {
				//	$item->options[$productID] = false;
					continue;
				}

				$adjustment = new OrderAdjustment();
				$adjustment->type = 'gift_options';
				$adjustment->name = $giftOption->title;
				$adjustment->description = $giftOption->title;
				$adjustment->sourceSnapshot = [
				    'gift_product_id'=>$productID,
				    'gift_option' => $giftOption->title.' '.\Craft::$app->getFormatter()->asCurrency($giftOption->defaultPrice, 'AUD', [], [], true)
                ]; // This can contain information about how the adjustment came to be
				$adjustment->amount = $giftOption->defaultPrice * $item->qty;
				$adjustment->setOrder($order);
				$adjustment->setLineItem($item);
				$adjustment->included = false;

//				$item->options['gift_option_price'] = $giftOption->defaultPrice;
//				$item->options['gift_option_title'] = $giftOption->title;

				$adjustments[] = $adjustment;
			}
		}

		return $adjustments;
	}
}