<?php

namespace modules\TwigExtensions\extensions;

use Craft;
use modules\Events\Elements\Event;
use modules\Events\Elements\Schedule;
use Twig\Extension\AbstractExtension;
use modules\TwigExtensions\traits\TwigExtensionsTrait;

class IsMembersOnly extends AbstractExtension
{
    use TwigExtensionsTrait;

    /**
     * @return string
     */
    public function getName()
    {
        return Craft::t('twigextensions', 'Members Only');
    }


    /**
     * @return array|\Twig_Filter[]
     */
    public function getFilters()
    {
        return [
            $this->addFilter('isMembersOnly'),
        ];
    }

    public function isMembersOnly($product) {
        if(!isset($product->memberPermission)) {
            return false;
        }

        foreach($product->memberPermission as $permission) {
            if($permission->selected && $permission->value == 'guest') {
                return false;
            }
        }

        return true;
    }
}
