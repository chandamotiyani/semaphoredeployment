<?php
/**
 * General Configuration
 *
 * All of your system's general configuration settings go in here. You can see a
 * list of the available settings in vendor/craftcms/cms/src/config/GeneralConfig.php.
 */

return [
    // Global settings
    '*' => [
        // Default Week Start Day (0 = Sunday, 1 = Monday...)
        'defaultWeekStartDay' => 0,

        // Enable CSRF Protection (recommended, will be enabled by default in Craft 3)
        'enableCsrfProtection' => true,

        // Whether "index.php" should be visible in URLs
        'omitScriptNameInUrls' => true,

        // Control Panel trigger word
        'cpTrigger' => 'admin',

        // The secure key Craft will use for hashing and encrypting data
        'securityKey' => getenv('SECURITY_KEY'),

        'aliases' => [
            '@assetBasePath' => getenv('ASSET_BASE_PATH'),
            '@assetBaseUrl' => getenv('ASSET_BASE_URL'),
            '@webroot' => dirname(__DIR__) . '/web',
        ],

        'useProjectConfigFile' => false,

        'pluginName' => 'Navigation',

        'autoLoginAfterAccountActivation' => true,

        'useEmailAsUsername' => true,

        'logoutPath' => 'logout',

        'setPasswordPath' => 'members/set-password',

        'setPasswordSuccessPath' => 'members/set-password-success',

        'invalidUserTokenPath' => 'members/set-password-error',

        'loginPath' => '/members/sign-in',

        'postLoginRedirect' => '/members/my-membership',

        'rememberedUserSessionDuration'=>false,

        'verificationCodeDuration'=>(60*60*24)*7, //7 days (in seconds).

        'pdfAllowRemoteImages' => true,

    ],

    'dev' => [
        // Dev Mode (see https://craftcms.com/guides/what-dev-mode-does)
        'devMode' => true,
  	'runQueueAutomatically'=>false,
    ],

    // Staging environment settings
    'staging' => [
        // Set this to `false` to prevent administrative changes from being made on staging
        'allowAdminChanges' => true,
    ],

    // Production environment settings
    'production' => [
        // Set this to `false` to prevent administrative changes from being made on production
        'allowAdminChanges' => true,
        'backupCommand' => false,
	'runQueueAutomatically'=>false,
    ],
];
