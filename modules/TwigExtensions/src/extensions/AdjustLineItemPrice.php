<?php

namespace modules\TwigExtensions\extensions;

use Craft;
use Twig\Extension\AbstractExtension;
use modules\TwigExtensions\traits\TwigExtensionsTrait;
use craft\commerce\Plugin as CommercePlugin;

class AdjustLineItemPrice extends AbstractExtension
{
    use TwigExtensionsTrait;

    /**
     * @return string
     */
    public function getName()
    {
        return Craft::t('twigextensions', 'Adjust Line Item Price');
    }

        /**
     * @return array|\Twig_Filter[]
     */
    public function getFunctions()
    {
        return [
            $this->addFunction('includeAdjusterPrice'),
        ];
    }

    /**
     * @param Object $lineitem line item
     * @return String
     * Returns Price including adjusters related to that product.
     */
    public function includeAdjusterPrice($lineItem) {

        if($lineItem->adjustments) {

            $adjustments = array_reduce($lineItem->adjustments, function($carry, $item) {
               $carry += $item->amount;

                return $carry;
            });

            return $lineItem->subtotal + $adjustments;
        }

        return $lineItem->subtotal;
    }
}
