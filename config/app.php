<?php
/**
 * Yii Application Config
 *
 * Edit this file at your own risk!
 *
 * The array returned by this file will get merged with
 * vendor/craftcms/cms/src/config/app/main.php and [web|console].php, when
 * Craft's bootstrap script is defining the configuration for the entire
 * application.
 *
 * You can define custom modules and system components, and even override the
 * built-in system components.
 */

return [

    // All environments
    '*' => [
        'aliases' => [
            '@assetBasePath' => getenv('ASSET_BASE_PATH'),
            '@assetBaseUrl' => getenv('ASSET_BASE_URL'),
        ],
        'modules'   => [
            'site-module' => [
                'class' => \modules\SiteModule\SiteModule::class,
            ],
            'twig-extensions' => [
                'class' => \modules\TwigExtensions\TwigExtensions::class,
            ],
            'memberships' => \modules\Memberships\MembershipModule::class,
            'events' => \modules\Events\EventsModule::class,
            'spreedly' => \modules\Spreedly\SpreedlyModule::class,
  	        'rezdy'=>\modules\Rezdy\RezdyModule::class,
  	        'importer'=>\modules\Importer\ImporterModule::class,
  	        'orders'=>\modules\Orders\OrdersModule::class,
            'products'=>\modules\Products\ProductsModule::class,
  	        'gift-options' =>\modules\GiftOptions\GiftOptionsModule::class,
  	        'yalumba-api'=>\modules\Yalumba\YalumbaApiModule::class,
            'vend-api'=>\modules\Vend\VendModule::class,
            'api-log'=>\modules\ApiLog\ApiLogModule::class,
            'free-shipping' =>\modules\FreeShipping\FreeShippingModule::class,
        ],
        'bootstrap' => [
            'site-module',
            'twig-extensions',
            'events',
            'spreedly',
            'rezdy',
            'memberships',
            'gift-options',
            'importer',
            'orders',
            'products',
            'yalumba-api',
            'vend-api',
            'api-log',
            'free-shipping'
        ],
    ],

    // Live (production) environment
    'production'  => [
      'components' => [
            'redis' => [
                'class' => yii\redis\Connection::class,
                'hostname' => getenv('REDIS_HOST'),
                'port' => 6379,
                'database' => 0,
            ],
            'cache' => [
                'class' => yii\redis\Cache::class,
                'defaultDuration' => 86400,
                'keyPrefix' => '7sh4hg9We2_prod_',
            ],

            'session' => function() {
            // Get the default component config
            $config = craft\helpers\App::sessionConfig();

            // Override the class to use Redis' session class
            $config['class'] = yii\redis\Session::class;

            // Instantiate and return it
            return Craft::createObject($config);
        },
        ],

    ],

    // Staging (pre-production) environment
    'staging'  => [
        // Default to database 0, so PHP sessions are in a separate database
        'components' => [
            'redis' => [
                'class' => yii\redis\Connection::class,
                'hostname' => 'yal-website-staging.hkmasw.0001.apse2.cache.amazonaws.com',
                'port' => 6379,
                'database' => 0,
            ],
            // Use database 2 for staging
            'cache' => [
                'class' => yii\redis\Cache::class,
                'redis' => [
                    'hostname' => 'yal-website-staging.hkmasw.0001.apse2.cache.amazonaws.com',
                    'port' => 6379,
                    'database' => 2,
                ],
            ],
            'session' => [
                'class' => yii\redis\Session::class,
                'as session' => craft\behaviors\SessionBehavior::class,
            ],
        ],
    ],

    // Local (development) environment
    'local'  => [
    ],
];
