<?php

namespace modules\TwigExtensions\extensions;

use Craft;
use Twig\Extension\AbstractExtension;
use modules\TwigExtensions\traits\TwigExtensionsTrait;
use craft\web\Request;

class SocialShare extends AbstractExtension
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
            $this->addFunction('socialShare'),
        ];
    }


    /**
     * @param $var
     * @param $instance
     * @return bool
     */
    public function socialShare($service) {
        $request = new Request;
        $encodedUrl = urlencode($request->absoluteUrl);

        switch ($service) {
            case 'facebook':
                return 'https://www.facebook.com/sharer/sharer.php?u='.$encodedUrl;
                break;
            case 'twitter':
                    return 'https://twitter.com/intent/tweet?text='.$encodedUrl;
                break;
            case 'linkedin':
                return 'https://www.linkedin.com/shareArticle?mini=true&title=&summary=&source=&url='.$encodedUrl;
                break;
            default:
                return null;
        }
    }

}
