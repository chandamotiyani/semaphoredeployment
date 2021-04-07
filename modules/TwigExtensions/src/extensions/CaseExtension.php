<?php

namespace modules\TwigExtensions\extensions;

use Craft;
use Twig\Extension\AbstractExtension;
use modules\TwigExtensions\traits\TwigExtensionsTrait;

class CaseExtension extends AbstractExtension
{
    use TwigExtensionsTrait;

    /**
     * @return string
     */
    public function getName()
    {
        return Craft::t('twigextensions', 'Case Extension');
    }

    /**
     * @return array|\Twig_Filter[]
     */
    public function getFilters()
    {
        return [
            $this->addFilter('camelCase'),
            $this->addFilter('studlyCase'),
            $this->addFilter('kebabCase'),
            $this->addFilter('isNumeric'),
        ];
    }

    /**
     * @param string $string
     * @return string
     */
    public function studlyCase(string $string):string
    {
        return str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $string)));
    }

    /**
     * @param string $string
     * @return string
     */
    public function kebabCase(string $string):string
    {
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '-$0', $string));
    }

    /**
     * @param string $string
     * @return string
     */
    public function camelCase(string $string):string
    {
        return lcfirst($this->studlyCase($string));
    }

    public function isNumeric($var) {
        return is_numeric($var);
    }
}
