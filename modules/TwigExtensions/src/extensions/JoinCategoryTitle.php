<?php

namespace modules\TwigExtensions\extensions;

use Craft;
use Twig\Extension\AbstractExtension;
use modules\TwigExtensions\traits\TwigExtensionsTrait;

class JoinCategoryTitle extends AbstractExtension
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
            $this->addFilter('joinCategoryTitle'),
            $this->addFilter('gaItemJs'),
            $this->addFilter('gaTransactionJs'),
        ];
    }

    /**
     * Groups wishlist items by handle
     */
    public function joinCategoryTitle($category, $level = 2) {

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
}