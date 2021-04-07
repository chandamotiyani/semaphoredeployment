<?php

namespace modules\TwigExtensions\extensions;

use Craft;
use modules\Events\Elements\Event;
use modules\Events\Elements\Schedule;
use Twig\Extension\AbstractExtension;
use modules\TwigExtensions\traits\TwigExtensionsTrait;

class SortBy extends AbstractExtension
{
    use TwigExtensionsTrait;

    /**
     * @return string
     */
    public function getName()
    {
        return Craft::t('twigextensions', 'Instance Of');
    }

    /**
     * @return array|\Twig_Filter[]
     */
    public function getFunctions()
    {
        return [
            $this->addFunction('sortBy'),
        ];
    }

        /**
     * @return array|\Twig_Filter[]
     */
    public function getFilters()
    {
        return [
            $this->addFilter('filterByGroupHandle'),
            $this->addFilter('filterByProductType'),
        ];
    }

    /**
     * @param $var
     * @param $instance
     * @return bool
     */
    public function sortBy($key, $array) {
        usort($array, function ($item1, $item2) use ($key) {
            if ($item1[$key] == $item2[$key]) return 0;
            return $item1[$key] < $item2[$key] ? -1 : 1;
        });
        return $array;
    }

    /**
     * Groups wishlist items by handle
     */
    public function filterByProductType($items, $handle = '') {
        // convert handle to an array
        if(! is_array($handle)) {
            $handle = [$handle];
        }

        // Filter items by handle
        $items = array_filter($items, function($item) use($handle) {
            return in_array($item->element->type->handle, $handle);
        });

        // Return the items as Elements ($items are originally lineItems)
        return array_map(function($item) {
            return $item->element;
        }, $items);
    }

    public function filterByGroupHandle($lineItems, $handle = '') {
        // convert handle to an array
        if(! is_array($handle)) {
            $handle = [$handle];
        }

        return array_filter($lineItems, function($lineItem) use($handle) {
            if( is_a($lineItem->purchasable, Schedule::class) ) {
                return in_array($lineItem->purchasable->getEvent()->groupHandle, $handle);
            }

            return in_array('products', $handle);
        });
    }
}
