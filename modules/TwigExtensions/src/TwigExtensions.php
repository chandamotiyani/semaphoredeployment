<?php

namespace modules\TwigExtensions;

use Craft;
use yii\base\Module;
use craft\i18n\PhpMessageSource;
use modules\TwigExtensions\extensions\CaseExtension;
use modules\TwigExtensions\extensions\ChunkByPropertyExtension;
use modules\TwigExtensions\extensions\FilterSaleTypes;
use modules\TwigExtensions\extensions\isInstanceOf;
use modules\TwigExtensions\extensions\AdjustLineItemPrice;
use modules\TwigExtensions\extensions\SortBy;
use modules\TwigExtensions\extensions\IsMembersOnly;
use modules\TwigExtensions\extensions\SocialShare;
use modules\TwigExtensions\extensions\JoinCategoryTitle;
use modules\TwigExtensions\extensions\GATracking;
use modules\TwigExtensions\extensions\CacheBust;
use modules\TwigExtensions\extensions\CheckDiscount;
use modules\TwigExtensions\extensions\getProductSearchKeywords;

class TwigExtensions extends Module
{
    /**
     * @var TwigExtensionsModule
     */
    public static $instance;

    /**
     * The available extensions
     *
     * @var array
     */
    public $extensions = [
        CaseExtension::class,
        ChunkByPropertyExtension::class,
        JoinCategoryTitle::class,
        FilterSaleTypes::class,
        isInstanceOf::class,
        AdjustLineItemPrice::class,
        SortBy::class,
        IsMembersOnly::class,
        SocialShare::class,
        GATracking::class,
        CacheBust::class,
        getProductSearchKeywords::class,
        CheckDiscount::class,
    ];

    /**
     * @inheritdoc
     */
    public function __construct($id, $parent = null, array $config = [])
    {
        Craft::setAlias('@modules/TwigExtensions', $this->getBasePath());
        $this->controllerNamespace = 'modules\TwigExtensions\controllers';

        // Set translation category
        $this->setI18n($id);

        // Set this as the global instance of this module class
        static::setInstance($this);

        parent::__construct($id, $parent, $config);
    }

    public function init()
    {
        parent::init();
        self::$instance = $this;

        $settings = Craft::$app->config->getConfigFromFile('TwigExtensions');

        $this->registerExtensions(
            array_diff($this->extensions, $settings['disabled'] ?? [])
        );
    }

    /**
     * Register twig extensions
     *
     * @param array $extensions
     */
    private function registerExtensions(array $extensions)
    {
        foreach ($extensions as $extension) {
            Craft::$app->view->registerTwigExtension(new $extension);
        }
    }

    /**
     * Set the internationalization category
     *
     * @param $id - module id
     */
    private function setI18n($id) {
        // Translation category
        $i18n = Craft::$app->getI18n();
        /** @noinspection UnSafeIsSetOverArrayInspection */
        if (! isset($i18n->translations[$id]) && ! isset($i18n->translations[$id.'*'])) {
            $i18n->translations[$id] = [
                'class' => PhpMessageSource::class,
                'sourceLanguage' => 'en-US',
                'basePath' => '@modules/TwigExtensions/translations',
                'forceTranslation' => true,
                'allowOverrides' => true,
            ];
        }
    }
}
