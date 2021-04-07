<?php

namespace modules\TwigExtensions\extensions;

use Craft;
use Twig\Extension\AbstractExtension;
use modules\TwigExtensions\traits\TwigExtensionsTrait;
use craft\commerce\Plugin as CommercePlugin;

class CheckDiscount extends AbstractExtension
{
    use TwigExtensionsTrait;

    /**
     * @return string
     */
    public function getName()
    {
        return Craft::t('twigextensions', 'Check if a cart\'s discount has been applied');
    }

        /**
     * @return array|\Twig_Filter[]
     */
    public function getFunctions()
    {
        return [
            $this->addFunction('hasDiscountApplied'),
        ];
    }

    /**
     * Check to see if a coupon is actually applied to the order. Some products are 'non promotable' which means coupons will have no effect on them. You CAN use up a coupon and have no discount applied if you have only 'non promotable' items in your order.
     */
    public function hasDiscountApplied($cart) {

        if(! $cart->couponCode) {
            return;
        }

        $currentDiscount = CommercePlugin::getInstance()->discounts->getDiscountByCode($cart->couponCode);

        if($currentDiscount) {

            // if it's got free shipping, it's of some use so allow it.
            if($currentDiscount->hasFreeShippingForOrder) {
                return 1;
            }

            // if it's not per item or percent, it's off the order as a whole so allow it.
            if($currentDiscount->perItemDiscount*1 == 0 && $currentDiscount->percentDiscount*1 == 0) {
                return 1;
            }


            // check to see if products are discountable.
            return array_reduce($cart->lineItems, function($isDiscountable, $item) use($currentDiscount) {

                if (CommercePlugin::getInstance()->getDiscounts()->matchLineItem($item, $currentDiscount, false)) {
                    $isDiscountable += $item->discount;
                }

                 return $isDiscountable;
             });
        }

    }
}
