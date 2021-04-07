<?php

$vendorDir = dirname(__DIR__);
$rootDir = dirname(dirname(__DIR__));

return array (
  'craftcms/aws-s3' => 
  array (
    'class' => 'craft\\awss3\\Plugin',
    'basePath' => $vendorDir . '/craftcms/aws-s3/src',
    'handle' => 'aws-s3',
    'aliases' => 
    array (
      '@craft/awss3' => $vendorDir . '/craftcms/aws-s3/src',
    ),
    'name' => 'Amazon S3',
    'version' => '1.2.11',
    'description' => 'Amazon S3 integration for Craft CMS',
    'developer' => 'Pixel & Tonic',
    'developerUrl' => 'https://pixelandtonic.com/',
    'developerEmail' => 'support@craftcms.com',
    'documentationUrl' => 'https://github.com/craftcms/aws-s3/blob/master/README.md',
  ),
  'craftcms/element-api' => 
  array (
    'class' => 'craft\\elementapi\\Plugin',
    'basePath' => $vendorDir . '/craftcms/element-api/src',
    'handle' => 'element-api',
    'aliases' => 
    array (
      '@craft/elementapi' => $vendorDir . '/craftcms/element-api/src',
    ),
    'name' => 'Element API',
    'version' => '2.6.0',
    'description' => 'Create a JSON API for your elements in Craft',
    'developer' => 'Pixel & Tonic',
    'developerUrl' => 'https://pixelandtonic.com/',
    'developerEmail' => 'support@craftcms.com',
    'documentationUrl' => 'https://github.com/craftcms/element-api/blob/v2/README.md',
  ),
  'doublesecretagency/craft-inventory' => 
  array (
    'class' => 'doublesecretagency\\inventory\\Inventory',
    'basePath' => $vendorDir . '/doublesecretagency/craft-inventory/src',
    'handle' => 'inventory',
    'aliases' => 
    array (
      '@doublesecretagency/inventory' => $vendorDir . '/doublesecretagency/craft-inventory/src',
    ),
    'name' => 'Inventory',
    'version' => '2.1.1',
    'schemaVersion' => '2.0.0',
    'description' => 'Take stock of your field usage.',
    'developer' => 'Double Secret Agency',
    'developerUrl' => 'https://www.doublesecretagency.com/plugins',
    'documentationUrl' => 'https://github.com/doublesecretagency/craft-inventory/blob/v2/README.md',
    'changelogUrl' => 'https://raw.githubusercontent.com/doublesecretagency/craft-inventory/v2/CHANGELOG.md',
  ),
  'craftcms/contact-form' => 
  array (
    'class' => 'craft\\contactform\\Plugin',
    'basePath' => $vendorDir . '/craftcms/contact-form/src',
    'handle' => 'contact-form',
    'aliases' => 
    array (
      '@craft/contactform' => $vendorDir . '/craftcms/contact-form/src',
    ),
    'name' => 'Contact Form',
    'version' => '2.2.7',
    'description' => 'Add a simple contact form to your Craft CMS site',
    'developer' => 'Pixel & Tonic',
    'developerUrl' => 'https://pixelandtonic.com/',
    'developerEmail' => 'support@craftcms.com',
    'documentationUrl' => 'https://github.com/craftcms/contact-form/blob/v2/README.md',
    'components' => 
    array (
      'mailer' => 'craft\\contactform\\Mailer',
    ),
  ),
  'isev-ltd/contact-form-validation' => 
  array (
    'class' => 'Isev\\ContactFormValidation\\Plugin',
    'basePath' => $vendorDir . '/isev-ltd/contact-form-validation/src',
    'handle' => 'contact-form-validation',
    'aliases' => 
    array (
      '@Isev/ContactFormValidation' => $vendorDir . '/isev-ltd/contact-form-validation/src',
    ),
    'name' => 'Contact Form Validation',
    'version' => '0.0.1',
    'description' => 'Enhanced Validation for Craft CMS Contact Form Plugin',
    'developer' => 'isev',
    'developerUrl' => 'https://isev.co.uk',
    'developerEmail' => 'mitch@isev.co.uk',
  ),
  'jalendport/craft-queuemanager' => 
  array (
    'class' => 'jalendport\\queuemanager\\QueueManager',
    'basePath' => $vendorDir . '/jalendport/craft-queuemanager/src',
    'handle' => 'queue-manager',
    'aliases' => 
    array (
      '@jalendport/queuemanager' => $vendorDir . '/jalendport/craft-queuemanager/src',
    ),
    'name' => 'Queue Manager',
    'version' => '1.2.0',
    'description' => 'Job Queue Manager for Craft CMS.',
    'developer' => 'Jalen Davenport',
    'developerUrl' => 'https://jalendport.com',
    'changelogUrl' => 'https://raw.githubusercontent.com/jalendport/craft-queuemanager/master/CHANGELOG.md',
    'hasCpSettings' => false,
    'hasCpSection' => true,
  ),
  'nystudio107/craft-templatecomments' => 
  array (
    'class' => 'nystudio107\\templatecomments\\TemplateComments',
    'basePath' => $vendorDir . '/nystudio107/craft-templatecomments/src',
    'handle' => 'templatecomments',
    'aliases' => 
    array (
      '@nystudio107/templatecomments' => $vendorDir . '/nystudio107/craft-templatecomments/src',
    ),
    'name' => 'Template Comments',
    'version' => '1.1.2',
    'description' => 'Adds a HTML comment with performance timings to demarcate `{% block %}`s and each Twig template that is included or extended.',
    'developer' => 'nystudio107',
    'developerUrl' => 'https://nystudio107.com/',
    'changelogUrl' => 'https://raw.githubusercontent.com/nystudio107/craft-templatecomments/v1/CHANGELOG.md',
    'hasCpSettings' => false,
    'hasCpSection' => false,
  ),
  'nystudio107/craft-typogrify' => 
  array (
    'class' => 'nystudio107\\typogrify\\Typogrify',
    'basePath' => $vendorDir . '/nystudio107/craft-typogrify/src',
    'handle' => 'typogrify',
    'aliases' => 
    array (
      '@nystudio107/typogrify' => $vendorDir . '/nystudio107/craft-typogrify/src',
    ),
    'name' => 'Typogrify',
    'version' => '1.1.18',
    'schemaVersion' => '1.0.0',
    'description' => 'Typogrify prettifies your web typography by preventing ugly quotes and \'widows\' and more',
    'developer' => 'nystudio107',
    'developerUrl' => 'https://nystudio107.com/',
    'changelogUrl' => 'https://raw.githubusercontent.com/nystudio107/craft-typogrify/v1/CHANGELOG.md',
    'hasCpSettings' => false,
    'hasCpSection' => false,
    'components' => 
    array (
      'typogrify' => 'nystudio107\\typogrify\\services\\TypogrifyService',
    ),
  ),
  'studioespresso/craft-navigate' => 
  array (
    'class' => 'studioespresso\\navigate\\Navigate',
    'basePath' => $vendorDir . '/studioespresso/craft-navigate/src',
    'handle' => 'navigate',
    'aliases' => 
    array (
      '@studioespresso/navigate' => $vendorDir . '/studioespresso/craft-navigate/src',
    ),
    'name' => 'Navigate',
    'version' => '2.3.0',
    'description' => 'Navigation plugin for Craft 3',
    'developer' => 'Studio Espresso',
    'developerUrl' => 'https://studioespresso.co',
    'changelogUrl' => 'https://raw.githubusercontent.com/studioespresso/craft3-navigate/master/CHANGELOG.md',
    'hasCpSettings' => true,
    'hasCpSection' => true,
    'components' => 
    array (
      'navigate' => 'studioespresso\\navigate\\services\\NavigateService',
      'nodes' => 'studioespresso\\navigate\\services\\NodesService',
    ),
  ),
  'studioespresso/craft-seeder' => 
  array (
    'class' => 'studioespresso\\seeder\\Seeder',
    'basePath' => $vendorDir . '/studioespresso/craft-seeder/src',
    'handle' => 'seeder',
    'aliases' => 
    array (
      '@studioespresso/seeder' => $vendorDir . '/studioespresso/craft-seeder/src',
    ),
    'name' => 'Seeder',
    'version' => '3.3.1',
    'description' => 'Easy entries seeder for Craft CMS',
    'developer' => 'Studio Espresso',
    'developerUrl' => 'https://www.studioepsresso.co',
    'documentationUrl' => 'https://github.com/studioespresso/craft3-seeder/blob/master/README.md',
    'changelogUrl' => 'https://raw.githubusercontent.com/studioespresso/craft3-seeder/master/CHANGELOG.md',
    'hasCpSettings' => false,
    'hasCpSection' => true,
  ),
  'superbig/craft-entry-instructions' => 
  array (
    'class' => 'superbig\\entryinstructions\\EntryInstructions',
    'basePath' => $vendorDir . '/superbig/craft-entry-instructions/src',
    'handle' => 'entry-instructions',
    'aliases' => 
    array (
      '@superbig/entryinstructions' => $vendorDir . '/superbig/craft-entry-instructions/src',
    ),
    'name' => 'Entry Instructions',
    'version' => '1.0.7',
    'schemaVersion' => '1.0.0',
    'description' => 'A simple fieldtype to add instructions',
    'developer' => 'Superbig',
    'developerUrl' => 'https://superbig.co',
    'changelogUrl' => 'https://raw.githubusercontent.com/sjelfull/craft-entry-instructions/master/CHANGELOG.md',
    'hasCpSettings' => false,
    'hasCpSection' => false,
  ),
  'spicyweb/craft-fieldlabels' => 
  array (
    'class' => 'spicyweb\\fieldlabels\\Plugin',
    'basePath' => $vendorDir . '/spicyweb/craft-fieldlabels/src',
    'handle' => 'fieldlabels',
    'aliases' => 
    array (
      '@spicyweb/fieldlabels' => $vendorDir . '/spicyweb/craft-fieldlabels/src',
    ),
    'name' => 'Field Labels',
    'version' => '1.3.1.2',
    'schemaVersion' => '1.3.0',
    'description' => 'Override Craft CMS field labels and instructions in the field layout designer',
    'developer' => 'Spicy Web',
    'developerUrl' => 'https://spicyweb.com.au/',
    'changelogUrl' => 'https://github.com/spicywebau/craft-fieldlabels/blob/master/CHANGELOG.md',
    'downloadUrl' => 'https://github.com/spicywebau/craft-fieldlabels/archive/master.zip',
  ),
  'am-impact/amcommand' => 
  array (
    'class' => 'amimpact\\commandpalette\\CommandPalette',
    'basePath' => $vendorDir . '/am-impact/amcommand/src',
    'handle' => 'command-palette',
    'aliases' => 
    array (
      '@amimpact/commandpalette' => $vendorDir . '/am-impact/amcommand/src',
    ),
    'name' => 'Command Palette',
    'version' => '3.1.4',
    'schemaVersion' => '3.0.0',
    'description' => 'Command palette in Craft.',
    'developer' => 'a&m impact',
    'developerUrl' => 'http://www.am-impact.nl',
    'documentationUrl' => 'https://github.com/am-impact/amcommand/blob/master/README.md',
    'changelogUrl' => 'https://raw.githubusercontent.com/am-impact/amcommand/craft3/CHANGELOG.md',
    'hasCpSection' => true,
    'components' => 
    array (
      'entries' => 'amimpact\\commandpalette\\services\\Entries',
      'general' => 'amimpact\\commandpalette\\services\\General',
      'globals' => 'amimpact\\commandpalette\\services\\Globals',
      'plugins' => 'amimpact\\commandpalette\\services\\Plugins',
      'search' => 'amimpact\\commandpalette\\services\\Search',
      'settings' => 'amimpact\\commandpalette\\services\\Settings',
      'tasks' => 'amimpact\\commandpalette\\services\\Tasks',
      'utilities' => 'amimpact\\commandpalette\\services\\Utilities',
      'users' => 'amimpact\\commandpalette\\services\\Users',
    ),
  ),
  'charliedev/element-map' => 
  array (
    'class' => 'charliedev\\elementmap\\ElementMap',
    'basePath' => $vendorDir . '/charliedev/element-map/src',
    'handle' => 'element-map',
    'aliases' => 
    array (
      '@charliedev/elementmap' => $vendorDir . '/charliedev/element-map/src',
    ),
    'name' => 'Element Map',
    'version' => '1.2.1',
    'schemaVersion' => '1.0.0',
    'description' => 'Simple visualization panel that lists related elements in their associated editors.',
    'developer' => 'Charlie Development',
    'developerUrl' => 'http://charliedev.com/',
    'changelogUrl' => 'https://raw.githubusercontent.com/charliedevelopment/craft3-element-map/master/CHANGELOG.md',
    'downloadUrl' => 'https://github.com/charliedevelopment/craft3-element-map/archive/master.zip',
    'hasCpSettings' => false,
    'hasCpSection' => false,
  ),
  'nystudio107/craft-twigprofiler' => 
  array (
    'class' => 'nystudio107\\twigprofiler\\TwigProfiler',
    'basePath' => $vendorDir . '/nystudio107/craft-twigprofiler/src',
    'handle' => 'twig-profiler',
    'aliases' => 
    array (
      '@nystudio107/twigprofiler' => $vendorDir . '/nystudio107/craft-twigprofiler/src',
    ),
    'name' => 'Twig Profiler',
    'version' => '1.0.1',
    'description' => 'Twig Profiler allows you to profile sections of your Twig templates, and see the resulting timings in the Yii2 Debug Toolbar',
    'developer' => 'nystudio107',
    'developerUrl' => 'https://nystudio107.com/',
    'changelogUrl' => 'https://raw.githubusercontent.com/nystudio107/craft-twigprofiler/master/CHANGELOG.md',
    'hasCpSettings' => false,
    'hasCpSection' => false,
    'components' => 
    array (
      'profile' => 'nystudio107\\twigprofiler\\services\\Profile',
    ),
  ),
  'verbb/navigation' => 
  array (
    'class' => 'verbb\\navigation\\Navigation',
    'basePath' => $vendorDir . '/verbb/navigation/src',
    'handle' => 'navigation',
    'aliases' => 
    array (
      '@verbb/navigation' => $vendorDir . '/verbb/navigation/src',
    ),
    'name' => 'Navigation',
    'version' => '1.4.14',
    'description' => 'A Craft CMS plugin to create navigation menus for your site.',
    'developer' => 'Verbb',
    'developerUrl' => 'https://verbb.io',
    'changelogUrl' => 'https://raw.githubusercontent.com/verbb/navigation/craft-3/CHANGELOG.md',
  ),
  'spicyweb/craft-neo' => 
  array (
    'class' => 'benf\\neo\\Plugin',
    'basePath' => $vendorDir . '/spicyweb/craft-neo/src',
    'handle' => 'neo',
    'aliases' => 
    array (
      '@benf/neo' => $vendorDir . '/spicyweb/craft-neo/src',
    ),
    'name' => 'Neo',
    'version' => '2.9.2',
    'schemaVersion' => '2.8.16',
    'description' => 'A Matrix-like field type that uses existing fields',
    'developer' => 'Spicy Web',
    'developerUrl' => 'https://github.com/spicywebau',
    'changelogUrl' => 'https://github.com/spicywebau/craft-neo/blob/master/CHANGELOG.md',
    'downloadUrl' => 'https://github.com/spicywebau/craft-neo/archive/master.zip',
  ),
  'craftcms/redactor' => 
  array (
    'class' => 'craft\\redactor\\Plugin',
    'basePath' => $vendorDir . '/craftcms/redactor/src',
    'handle' => 'redactor',
    'aliases' => 
    array (
      '@craft/redactor' => $vendorDir . '/craftcms/redactor/src',
    ),
    'name' => 'Redactor',
    'version' => '2.8.5',
    'description' => 'Edit rich text content in Craft CMS using Redactor by Imperavi.',
    'developer' => 'Pixel & Tonic',
    'developerUrl' => 'https://pixelandtonic.com/',
    'documentationUrl' => 'https://github.com/craftcms/redactor/blob/v2/README.md',
  ),
  'verbb/super-table' => 
  array (
    'class' => 'verbb\\supertable\\SuperTable',
    'basePath' => $vendorDir . '/verbb/super-table/src',
    'handle' => 'super-table',
    'aliases' => 
    array (
      '@verbb/supertable' => $vendorDir . '/verbb/super-table/src',
    ),
    'name' => 'Super Table',
    'version' => '2.6.7',
    'description' => 'Super-charge your Craft workflow with Super Table. Use it to group fields together or build complex Matrix-in-Matrix solutions.',
    'developer' => 'Verbb',
    'developerUrl' => 'https://verbb.io',
    'documentationUrl' => 'https://github.com/verbb/super-table',
    'changelogUrl' => 'https://raw.githubusercontent.com/verbb/super-table/craft-3/CHANGELOG.md',
  ),
  'verbb/cp-nav' => 
  array (
    'class' => 'verbb\\cpnav\\CpNav',
    'basePath' => $vendorDir . '/verbb/cp-nav/src',
    'handle' => 'cp-nav',
    'aliases' => 
    array (
      '@verbb/cpnav' => $vendorDir . '/verbb/cp-nav/src',
    ),
    'name' => 'Control Panel Nav',
    'version' => '3.0.15',
    'description' => 'Control Panel Nav helps you managing your Control Panel navigation.',
    'developer' => 'Verbb',
    'developerUrl' => 'http://verbb.io',
    'changelogUrl' => 'https://raw.githubusercontent.com/verbb/cp-nav/craft-3/CHANGELOG.md',
  ),
  'solspace/craft-freeform' => 
  array (
    'class' => 'Solspace\\Freeform\\Freeform',
    'basePath' => $vendorDir . '/solspace/craft-freeform/packages/plugin/src',
    'handle' => 'freeform',
    'aliases' => 
    array (
      '@Solspace/Freeform' => $vendorDir . '/solspace/craft-freeform/packages/plugin/src',
      '@Solspace/Tests/Freeform' => $vendorDir . '/solspace/craft-freeform/packages/plugin/tests',
    ),
    'name' => 'Freeform',
    'version' => '3.10.9',
    'schemaVersion' => '3.3.0',
    'description' => 'The most reliable, intuitive and powerful form builder for Craft.',
    'developer' => 'Solspace',
    'developerUrl' => 'https://docs.solspace.com/',
    'documentationUrl' => 'https://docs.solspace.com/craft/freeform/v3/',
    'changelogUrl' => 'https://raw.githubusercontent.com/solspace/craft3-freeform/master/CHANGELOG.md',
    'hasCpSection' => true,
  ),
  'nystudio107/craft-seomatic' => 
  array (
    'class' => 'nystudio107\\seomatic\\Seomatic',
    'basePath' => $vendorDir . '/nystudio107/craft-seomatic/src',
    'handle' => 'seomatic',
    'aliases' => 
    array (
      '@nystudio107/seomatic' => $vendorDir . '/nystudio107/craft-seomatic/src',
    ),
    'name' => 'SEOmatic',
    'version' => '3.3.35',
    'description' => 'SEOmatic facilitates modern SEO best practices & implementation for Craft CMS 3. It is a turnkey SEO system that is comprehensive, powerful, and flexible.',
    'developer' => 'nystudio107',
    'developerUrl' => 'https://nystudio107.com',
    'changelogUrl' => 'https://raw.githubusercontent.com/nystudio107/craft-seomatic/v3/CHANGELOG.md',
    'hasCpSettings' => true,
    'hasCpSection' => true,
    'components' => 
    array (
      'frontendTemplates' => 'nystudio107\\seomatic\\services\\FrontendTemplates',
      'helper' => 'nystudio107\\seomatic\\services\\Helper',
      'jsonLd' => 'nystudio107\\seomatic\\services\\JsonLd',
      'link' => 'nystudio107\\seomatic\\services\\Link',
      'metaBundles' => 'nystudio107\\seomatic\\services\\MetaBundles',
      'metaContainers' => 'nystudio107\\seomatic\\services\\MetaContainers',
      'seoElements' => 'nystudio107\\seomatic\\services\\SeoElements',
      'script' => 'nystudio107\\seomatic\\services\\Script',
      'sitemaps' => 'nystudio107\\seomatic\\services\\Sitemaps',
      'tag' => 'nystudio107\\seomatic\\services\\Tag',
      'title' => 'nystudio107\\seomatic\\services\\Title',
    ),
  ),
  'ostark/craft-async-queue' => 
  array (
    'class' => 'ostark\\AsyncQueue\\Plugin',
    'basePath' => $vendorDir . '/ostark/craft-async-queue/src',
    'handle' => 'async-queue',
    'aliases' => 
    array (
      '@ostark/AsyncQueue' => $vendorDir . '/ostark/craft-async-queue/src',
    ),
    'name' => 'AsyncQueue',
    'version' => '2.2.0',
    'description' => 'A queue handler that moves queue execution to a non-blocking background process',
    'developer' => 'Oliver Stark',
    'developerUrl' => 'https://www.fortrabbit.com',
    'changelogUrl' => 'https://raw.githubusercontent.com/ostark/craft-async-queue/master/CHANGELOG.md',
    'hasCpSettings' => false,
    'hasCpSection' => false,
  ),
  'studioespresso/craft-google-shopping-feed' => 
  array (
    'class' => 'studioespresso\\googleshoppingfeed\\GoogleShoppingFeed',
    'basePath' => $vendorDir . '/studioespresso/craft-google-shopping-feed/src',
    'handle' => 'google-shopping-feed',
    'aliases' => 
    array (
      '@studioespresso/googleshoppingfeed' => $vendorDir . '/studioespresso/craft-google-shopping-feed/src',
    ),
    'name' => 'Google Shopping Feed',
    'version' => '1.3.0',
    'description' => 'A Google Shopping feed for Craft Commerce',
    'developer' => 'Studio Espresso',
    'developerUrl' => 'https://studioespresso.dev',
    'hasCpSettings' => true,
    'hasCpSection' => false,
    'components' => 
    array (
      'elements' => 'studioespresso\\googleshoppingfeed\\services\\ElementsService',
    ),
  ),
  'mmikkel/cp-field-inspect' => 
  array (
    'class' => 'mmikkel\\cpfieldinspect\\CpFieldInspect',
    'basePath' => $vendorDir . '/mmikkel/cp-field-inspect/src',
    'handle' => 'cp-field-inspect',
    'aliases' => 
    array (
      '@mmikkel/cpfieldinspect' => $vendorDir . '/mmikkel/cp-field-inspect/src',
    ),
    'name' => 'CP Field Inspect',
    'version' => '1.2.3',
    'schemaVersion' => '1.0.0',
    'description' => 'Inspect field handles and easily edit field and element source settings',
    'developer' => 'Mats Mikkel Rummelhoff',
    'developerUrl' => 'http://mmikkel.no',
    'changelogUrl' => 'https://raw.githubusercontent.com/mmikkel/CpFieldInspect-Craft/master/CHANGELOG.md',
    'hasCpSettings' => false,
    'hasCpSection' => false,
  ),
  'craftcms/commerce' => 
  array (
    'class' => 'craft\\commerce\\Plugin',
    'basePath' => $vendorDir . '/craftcms/commerce/src',
    'handle' => 'commerce',
    'aliases' => 
    array (
      '@craft/commerce' => $vendorDir . '/craftcms/commerce/src',
      '@craftcommercetests/fixtures' => $vendorDir . '/craftcms/commerce/tests/fixtures',
    ),
    'name' => 'Craft Commerce',
    'version' => '3.2.11',
    'description' => 'Create beautifully bespoke ecommerce experiences',
    'developer' => 'Pixel & Tonic',
    'developerUrl' => 'https://craftcommerce.com',
    'documentationUrl' => 'https://craftcms.com/docs/commerce/3.x/',
  ),
  'verbb/wishlist' => 
  array (
    'class' => 'verbb\\wishlist\\Wishlist',
    'basePath' => $vendorDir . '/verbb/wishlist/src',
    'handle' => 'wishlist',
    'aliases' => 
    array (
      '@verbb/wishlist' => $vendorDir . '/verbb/wishlist/src',
    ),
    'name' => 'Wishlist',
    'version' => '1.4.4',
    'description' => 'Allow users to create lists to store favourite entries, save products in a wishlist and more.',
    'developer' => 'Verbb',
    'developerUrl' => 'https://verbb.io',
    'changelogUrl' => 'https://raw.githubusercontent.com/verbb/wishlist/craft-3/CHANGELOG.md',
  ),
);
