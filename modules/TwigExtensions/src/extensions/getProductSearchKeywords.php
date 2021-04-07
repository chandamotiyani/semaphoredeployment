<?php

namespace modules\TwigExtensions\extensions;

use Craft;
use Twig\Extension\AbstractExtension;
use modules\TwigExtensions\traits\TwigExtensionsTrait;

class getProductSearchKeywords extends AbstractExtension
{
    use TwigExtensionsTrait;

    /**
     * @return string
     */
    public function getName()
    {
    return Craft::t('twigextensions', 'Split Search Keywords For Product Query');
    }


    /**
     * @return array|\Twig_Filter[]
     */
    public function getFilters()
    {
        return [
            $this->addFilter('getProductSearchKeywords'),
        ];
    }

    public function getProductSearchKeywords($searchQuery) {
       $searchString = 'title:'.'*'.$searchQuery.'* OR regionsCategory:*'.$searchQuery.'*'.' OR specialReleases:*'.$searchQuery.'*';
       $keywords = explode(" ", $searchQuery);
       if(!empty($searchQuery) && count($keywords) > 1) {
           $searchString = "";
           $i = 0;
           foreach ($keywords as $kw) {
               $or = $i > 0 ? " OR " : "";
               $searchString .= $or.' title:'.'*'.$kw.'* OR regionsCategory:*'.$kw.'*'.' OR specialReleases:*'.$kw.'*';
               $i++;
           }
       }
       return $searchString;
    }
}