<?php

namespace modules\TwigExtensions\extensions;

use Craft;
use Twig\Extension\AbstractExtension;
use modules\TwigExtensions\traits\TwigExtensionsTrait;

class GATracking extends AbstractExtension
{
    use TwigExtensionsTrait;

    /**
     * @return string
     */
    public function getName()
    {
        return Craft::t('twigextensions', 'Join Title');
    }

    /**
     * @return array|\Twig_Filter[]
     */
    public function getFilters()
    {
        return [
            $this->addFilter('gaItemJs'),
            $this->addFilter('gaTransactionJs'),
        ];
    }

    public function joinTitle($category, $level = 2) {

        if(! $category) {
            return '';
        }

        if(empty($category->id) && is_object($category)) {
            $category = $category->all();
        }

        $filterCategory = array_filter($category, function($category) use($level) {
            if( $category->level == $level ) {
                return $category;
            }
        });

        if($filterCategory) {
            return join(' | ', array_column($filterCategory, 'title') );
        }

        return '';
    }

    public function lineItemCategory($lineItem) {
        if( method_exists($lineItem->purchasable, 'getEvent') ) {
            return $lineItem->purchasable->getEvent()->groupHandle;
        }

        return 'Wine Shop';
    }


    public function lineItemBrand($lineItem) {
        if(! empty($lineItem->purchasable->product->collections)) {
            return $this->joinTitle($lineItem->purchasable->product->collections);
        }

        return 'Yalumba';
    }

    // Function to return the JavaScript representation of a TransactionData object.
    public function gaTransactionJs($order) {
return <<< HTML
ga('ec:setAction', 'purchase', {
'id': `{$order->reference}`,
'affiliation': 'Yalumba',
'revenue': `{$order->itemTotal}`,
'shipping': `{$order->storedTotalShippingCost}`,
'tax': `{$order->storedTotalTaxIncluded}`,
});
HTML;
    }

    public function gaItemJs($item, $transId) {
return <<< HTML
ga('ec:addProduct', {
'id': `{$transId}`,
'name': `{$item['description']}`,
'price': `{$item['salePrice']}`,
'quantity': `{$item['qty']}`,
'brand': `{$this->lineItemBrand($item)}`,
'category': `{$this->lineItemCategory($item)}`,
});
HTML;
    }
}