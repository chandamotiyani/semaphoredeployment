<?php

namespace modules\TwigExtensions\extensions;

use Craft;
use Twig\Extension\AbstractExtension;
use modules\TwigExtensions\traits\TwigExtensionsTrait;

class CacheBust extends AbstractExtension
{
    use TwigExtensionsTrait;

    /**
     * @return string
     */
    public function getName()
    {
        return Craft::t('twigextensions', 'CacheBust');
    }

    /**
     * @return array|\Twig_Filter[]
     */
    public function getFilters()
    {
        return [
            $this->addFilter('cacheBust'),
        ];
    }

    /**
     * @param $fileName
     * @return string
     */
    public function cacheBust($fileName) {

        if(! $fileName) {
            return '';
        }

        $modified = date("Ymd", filemtime($fileName));

        return "{$fileName}?v={$modified}";
    }

}
