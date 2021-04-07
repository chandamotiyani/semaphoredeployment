# Advanced Usage

## Config Settings

SEOmatic supports the standard `config.php` multi-environment friendly config file for the plugin settings. Just copy the `config.php` to your Craft `config/` directory as `seomatic.php` and you can configure the settings in a multi-environment friendly way.

These are the same settings that are configured in the **Plugin Settings** in the Control Panel.

## Events

### IncludeContainerEvent

    const EVENT_INCLUDE_CONTAINER = 'includeContainer';

The event that is triggered when a container is about to be included.

```php
use nystudio107\seomatic\events\IncludeContainerEvent;
use nystudio107\seomatic\base\Container;
use yii\base\Event;
Event::on(Container::class, Container::EVENT_INCLUDE_CONTAINER, function(IncludeContainerEvent $e) {
    $e->include = false;
});
```

### InvalidateContainerCachesEvent

    const EVENT_INVALIDATE_CONTAINER_CACHES = 'invalidateContainerCaches';

The event that is triggered when SEOmatic is about to clear its meta container caches

```php
use nystudio107\seomatic\events\InvalidateContainerCachesEvent;
use nystudio107\seomatic\services\MetaContainers;
use yii\base\Event;
Event::on(MetaContainers::class, MetaContainers::EVENT_INVALIDATE_CONTAINER_CACHES, function(InvalidateContainerCachesEvent $e) {
    // Container caches are about to be cleared
});
```

### RegisterSitemapUrlsEvent

    const EVENT_REGISTER_SITEMAP_URLS = 'registerSitemapUrls';

The event that is triggered when registering additional URLs for a sitemap.

```php
use nystudio107\seomatic\events\RegisterSitemapUrlsEvent;
use nystudio107\seomatic\models\SitemapCustomTemplate;
use yii\base\Event;
Event::on(SitemapCustomTemplate::class, SitemapCustomTemplate::EVENT_REGISTER_SITEMAP_URLS, function(RegisterSitemapUrlsEvent $e) {
    $e->sitemaps[] = [
        'loc' => $url,
        'changefreq' => $changeFreq,
        'priority' => $priority,
        'lastmod' => $lastMod,
    ];
});
```

### RegisterSitemapsEvent

    const EVENT_REGISTER_SITEMAPS = 'registerSitemaps';

The event that is triggered when registering additional sitemaps for the sitemap index.

```php
use nystudio107\seomatic\events\RegisterSitemapsEvent;
use nystudio107\seomatic\models\SitemapIndexTemplate;
use yii\base\Event;
Event::on(SitemapIndexTemplate::class, SitemapIndexTemplate::EVENT_REGISTER_SITEMAPS, function(RegisterSitemapsEvent $e) {
    $e->sitemaps[] = [
        'loc' => $url,
        'lastmod' => $lastMod,
    ];
});
```

### RegisterComponentTypesEvent

    const EVENT_REGISTER_SEO_ELEMENT_TYPES = 'registerSeoElementTypes';

The event that is triggered when registering SeoElement types

SeoElement types must implement [[SeoElementInterface]]

```php
use nystudio107\seomatic\services\SeoElements;
use craft\events\RegisterComponentTypesEvent;
use yii\base\Event;

Event::on(SeoElements::class,
    SeoElements::EVENT_REGISTER_SEO_ELEMENT_TYPES,
    function(RegisterComponentTypesEvent $event) {
        $event->types[] = MySeoElement::class;
    }
);
```

### AddDynamicMetaEvent

    const EVENT_ADD_DYNAMIC_META = 'addDynamicMeta';

The event that is triggered when SEOmatic has included the standard meta containers, and gives your plugin/module the chance to add whatever custom dynamic meta items you like

```php
use nystudio107\seomatic\events\AddDynamicMetaEvent;
use nystudio107\seomatic\helpers\DynamicMeta;
use yii\base\Event;
Event::on(DynamicMeta::class, DynamicMeta::EVENT_INCLUDE_CONTAINER, function(AddDynamicMetaEvent $e) {
    // Add whatever dynamic meta items to the containers as you like
});
```

## Meta Bundle / Container Settings

The directory `vendor/nystudio107/seomatic/src/seomatic-config` contains a number of files that are used when initially configuring SEOmatic.

![Screenshot](./resources/screenshots/seomatic-seomatic-config.png)

You can copy this entire directory to your Craft `config/` directory, and customize the files to your heart's content. SEOmatic will first look in the `config/` directory for any given file, and then fall back on its own internal `seomatic-config` files.

Note that these files are only used when initially creating a meta bundle. That is, whenever the plugin is installed, or new Section, Category Groups, or Commerce Product Types are created. Once meta bundles have been created, changing the settings in the file will have no effect.

You can bump the `Bundle.php`'s `bundleVersion` setting if you want it to re-read your config settings.

## Headless SPA API

SEOmatic allows you to fetch the meta information for any page via a controller API endpoint, so you can render the meta data via a frontend framework like VueJS or React.

### GraphQL Query support

To retrieve SEOmatic container data through the native [GraphQL in Craft CMS 3.3](https://docs.craftcms.com/v3/graphql.html#sending-api-requests) or the [CraftQL plugin](https://github.com/markhuot/craftql), use the `seomatic` field in your graphql query. Each parameter will return that container's data, ready for insertion into the DOM.

You must as least pass in the URI you want metadata for:

```graphql
{
  seomatic (uri: "/") {
      metaTitleContainer
      metaTagContainer
      metaLinkContainer
      metaScriptContainer
      metaJsonLdContainer
      metaSiteVarsContainer
  }
}
```

...and you can also pass in an optional `siteId`:

```graphql
{
  seomatic (uri: "/", siteId: 2) {
      metaTitleContainer
      metaTagContainer
      metaLinkContainer
      metaScriptContainer
      metaJsonLdContainer
      metaSiteVarsContainer
  }
}
```

...or you can also pass in an optional `site` handle:

```graphql
{
  seomatic (uri: "/", site: "french") {
      metaTitleContainer
      metaTagContainer
      metaLinkContainer
      metaScriptContainer
      metaJsonLdContainer
      metaSiteVarsContainer
  }
}
```

...and you can also pass in an optional `asArray` parameter:

```graphql
{
  seomatic (uri: "/", asArray: true) {
      metaTitleContainer
      metaTagContainer
      metaLinkContainer
      metaScriptContainer
      metaJsonLdContainer
      metaSiteVarsContainer
  }
}
```
This defaults to `false` which returns to you HTML ready to be inserted into the DOM. If you set it to `true` then it will return a JSON-encoded array of the container data.

This is useful if you're using Next.js, Nuxt.js, Gatsby, Gridsome, or anything else that uses a library to insert the various tags. In this case, you want the raw data to pass along.

![Screenshot](./resources/screenshots/seomatic-craftql-query.png)

You can also piggyback on an entries query, to return all of your data for an entry as well as the SEOmatic metadata in one request.

**N.B.:** This requires using either Craft CMS 3.4 or later, or the CraftQL plugin to work.

Native Craft CMS GraphQL:

```graphql
{
  entry(section: "homepage") {
    id
    title
    seomatic {
      metaTitleContainer
      metaTagContainer
      metaLinkContainer
      metaScriptContainer
      metaJsonLdContainer
      metaSiteVarsContainer
    }
  }
}
```

CraftQL Plugin:

```graphql
{
  entry(section: homepage) {
    id
    title
    ... on Homepage {
      seomatic {
        metaTitleContainer
        metaTagContainer
        metaLinkContainer
        metaScriptContainer
        metaJsonLdContainer
      }
    }
  }
}
```

In this case, no arguments are passed in, because the URI and siteId will be taken from the parent Entry element. But you can pass in the `asArray` argument too:

Native Craft CMS GraphQL:

```graphql
{
  entry(section: "homepage") {
    id
    title
    seomatic(asArray: true) {
      metaTitleContainer
      metaTagContainer
      metaLinkContainer
      metaScriptContainer
      metaJsonLdContainer
      metaSiteVarsContainer
    }
  }
}
```

CraftQL Plugin:

```graphql
{
  entry(section: homepage) {
    id
    title
    ... on Homepage {
      seomatic(asArray: true) {
        metaTitleContainer
        metaTagContainer
        metaLinkContainer
        metaScriptContainer
        metaJsonLdContainer
      }
    }
  }
}
```


### Meta Container API Endpoints

**N.B.:** Anonymous access to the Meta Container endpoints are disabled by default; you'll need to enable them in SEOmatic → Plugin Settings → Endpoints

To get all of the meta containers for a given URI, the controller action is:

```twig
/actions/seomatic/meta-container/all-meta-containers/?uri=/
```
...where `uri` is the path to obtain the meta information from.

This will return to you an array of meta containers, with the render-ready meta tags in each:

```json
{
    "MetaTitleContainer": "<title>[devMode] Craft3 | Homepage</title>",
    "MetaTagContainer": "<meta name=\"generator\" content=\"SEOmatic\"><meta name=\"referrer\" content=\"no-referrer-when-downgrade\"><meta name=\"robots\" content=\"all\">",
    "MetaLinkContainer": "<link href=\"http://craft3.test/\" rel=\"canonical\"><link type=\"text/plain\" href=\"/humans.txt\" rel=\"author\"><link href=\"http://craft3.test/\" rel=\"alternate\" hreflang=\"es\">",
    "MetaScriptContainer": "",
    "MetaJsonLdContainer": "<script type=\"application/ld+json\">{\"@context\":\"http://schema.org\",\"@type\":\"WebPage\",\"image\":{\"@type\":\"ImageObject\",\"height\":\"804\",\"width\":\"1200\"},\"inLanguage\":\"en-us\",\"mainEntityOfPage\":\"http://craft3.test/\",\"name\":\"Homepage\",\"url\":\"http://craft3.test/\"}</script><script type=\"application/ld+json\">{\"@context\":\"http://schema.org\",\"@type\":\"BreadcrumbList\",\"description\":\"Breadcrumbs list\",\"itemListElement\":[{\"@type\":\"ListItem\",\"item\":{\"@id\":\"http://craft3.test/\",\"name\":\"Homepage\"},\"position\":1}],\"name\":\"Breadcrumbs\"}</script>",
    "MetaSiteVarsContainer": "{\"siteName\":\"woof\",\"identity\":{\"siteType\":\"Organization\",\"siteSubType\":\"LocalBusiness\",\"siteSpecificType\":\"none\",\"computedType\":\"LocalBusiness\",\"genericName\":\"\",\"genericAlternateName\":\"\",\"genericDescription\":\"\",\"genericUrl\":\"$HOME\",\"genericImage\":null,\"genericImageWidth\":\"229\",\"genericImageHeight\":\"220\",\"genericImageIds\":[\"25\"],\"genericTelephone\":\"\",\"genericEmail\":\"\",\"genericStreetAddress\":\"\",\"genericAddressLocality\":\"\",\"genericAddressRegion\":\"\",\"genericPostalCode\":\"\",\"genericAddressCountry\":\"\",\"genericGeoLatitude\":\"\",\"genericGeoLongitude\":\"\",\"personGender\":\"Male\",\"personBirthPlace\":\"\",\"organizationDuns\":\"\",\"organizationFounder\":\"\",\"organizationFoundingDate\":\"\",\"organizationFoundingLocation\":\"\",\"organizationContactPoints\":\"\",\"corporationTickerSymbol\":\"\",\"localBusinessPriceRange\":\"$\",\"localBusinessOpeningHours\":[{\"open\":null,\"close\":null},{\"open\":null,\"close\":null},{\"open\":null,\"close\":null},{\"open\":null,\"close\":null},{\"open\":null,\"close\":null},{\"open\":null,\"close\":null},{\"open\":null,\"close\":null}],\"restaurantServesCuisine\":\"\",\"restaurantMenuUrl\":\"\",\"restaurantReservationsUrl\":\"\"},\"creator\":{\"siteType\":\"Organization\",\"siteSubType\":\"LocalBusiness\",\"siteSpecificType\":\"none\",\"computedType\":\"LocalBusiness\",\"genericName\":\"\",\"genericAlternateName\":\"\",\"genericDescription\":\"\",\"genericUrl\":\"\",\"genericImage\":null,\"genericImageWidth\":\"1340\",\"genericImageHeight\":\"596\",\"genericImageIds\":[\"24\"],\"genericTelephone\":\"\",\"genericEmail\":\"\",\"genericStreetAddress\":\"\",\"genericAddressLocality\":\"\",\"genericAddressRegion\":\"\",\"genericPostalCode\":\"\",\"genericAddressCountry\":\"\",\"genericGeoLatitude\":\"\",\"genericGeoLongitude\":\"\",\"personGender\":\"Male\",\"personBirthPlace\":\"\",\"organizationDuns\":\"\",\"organizationFounder\":\"\",\"organizationFoundingDate\":\"\",\"organizationFoundingLocation\":\"\",\"organizationContactPoints\":\"\",\"corporationTickerSymbol\":\"\",\"localBusinessPriceRange\":\"$\",\"localBusinessOpeningHours\":[{\"open\":null,\"close\":null},{\"open\":null,\"close\":null},{\"open\":null,\"close\":null},{\"open\":null,\"close\":null},{\"open\":null,\"close\":null},{\"open\":null,\"close\":null},{\"open\":null,\"close\":null}],\"restaurantServesCuisine\":\"\",\"restaurantMenuUrl\":\"\",\"restaurantReservationsUrl\":\"\"},\"twitterHandle\":\"\",\"facebookProfileId\":\"\",\"facebookAppId\":\"\",\"googleSiteVerification\":\"\",\"bingSiteVerification\":\"\",\"pinterestSiteVerification\":\"\",\"sameAsLinks\":{\"twitter\":{\"siteName\":\"Twitter\",\"handle\":\"twitter\",\"url\":\"\"},\"facebook\":{\"siteName\":\"Facebook\",\"handle\":\"facebook\",\"url\":\"\"},\"wikipedia\":{\"siteName\":\"Wikipedia\",\"handle\":\"wikipedia\",\"url\":\"\"},\"linkedin\":{\"siteName\":\"LinkedIn\",\"handle\":\"linkedin\",\"url\":\"\"},\"googleplus\":{\"siteName\":\"Google+\",\"handle\":\"googleplus\",\"url\":\"\"},\"youtube\":{\"siteName\":\"YouTube\",\"handle\":\"youtube\",\"url\":\"\"},\"instagram\":{\"siteName\":\"Instagram\",\"handle\":\"instagram\",\"url\":\"\"},\"pinterest\":{\"siteName\":\"Pinterest\",\"handle\":\"pinterest\",\"url\":\"\"},\"github\":{\"siteName\":\"GitHub\",\"handle\":\"github\",\"url\":\"\"},\"vimeo\":{\"siteName\":\"Vimeo\",\"handle\":\"vimeo\",\"url\":\"\"}},\"siteLinksSearchTarget\":\"\",\"siteLinksQueryInput\":\"\",\"referrer\":\"no-referrer-when-downgrade\",\"additionalSitemapUrls\":[],\"additionalSitemapUrlsDateUpdated\":null,\"additionalSitemaps\":[]}"
}
```
If you need to request a URI from a specific site in a multi-site setup, you can do that with the optional `siteId=SITE_ID` parameter:

```
/actions/seomatic/meta-container/all-meta-containers/?uri=/&siteId=2
```

Should you wish to have the items in the meta containers return as an array of data instead, you can do that with the optional `asArray=true` parameter:

```
/actions/seomatic/meta-container/all-meta-containers/?uri=/&asArray=true
```

Which will return the data in array form:
```json
{
    "MetaTitleContainer": {
        "title": {
            "title": "[devMode] Craft3 | Homepage"
        }
    },
    "MetaTagContainer": {
        "generator": {
            "content": "SEOmatic",
            "name": "generator"
        },
        "referrer": {
            "content": "no-referrer-when-downgrade",
            "name": "referrer"
        },
        "robots": {
            "content": "all",
            "name": "robots"
        }
    },
    "MetaLinkContainer": {
        "canonical": {
            "href": "http://craft3.test/",
            "rel": "canonical"
        },
        "author": {
            "href": "/humans.txt",
            "rel": "author",
            "type": "text/plain"
        },
        "alternate": {
            "href": "http://craft3.test/",
            "hreflang": "es",
            "rel": "alternate"
        }
    },
    "MetaScriptContainer": [],
    "MetaJsonLdContainer": {
        "WebPage": {
            "@context": "http://schema.org",
            "@type": "WebPage",
            "image": {
                "@type": "ImageObject",
                "height": "804",
                "width": "1200"
            },
            "inLanguage": "en-us",
            "mainEntityOfPage": "http://craft3.test/",
            "name": "Homepage",
            "url": "http://craft3.test/"
        },
        "BreadcrumbList": {
            "@context": "http://schema.org",
            "@type": "BreadcrumbList",
            "description": "Breadcrumbs list",
            "itemListElement": [
                {
                    "@type": "ListItem",
                    "name": "Homepage",
                    "item": "http://craft3.test/",
                    "position": 1
                }
            ],
            "name": "Breadcrumbs"
        }
    },
    "MetaSiteVarsContainer": {
        "siteName": "woof",
        "identity": {
            "siteType": "Organization",
            "siteSubType": "LocalBusiness",
            "siteSpecificType": "none",
            "computedType": "LocalBusiness",
            "genericName": "",
            "genericAlternateName": "",
            "genericDescription": "",
            "genericUrl": "$HOME",
            "genericImage": null,
            "genericImageWidth": "229",
            "genericImageHeight": "220",
            "genericImageIds": [
                "25"
            ],
            "genericTelephone": "",
            "genericEmail": "",
            "genericStreetAddress": "",
            "genericAddressLocality": "",
            "genericAddressRegion": "",
            "genericPostalCode": "",
            "genericAddressCountry": "",
            "genericGeoLatitude": "",
            "genericGeoLongitude": "",
            "personGender": "Male",
            "personBirthPlace": "",
            "organizationDuns": "",
            "organizationFounder": "",
            "organizationFoundingDate": "",
            "organizationFoundingLocation": "",
            "organizationContactPoints": "",
            "corporationTickerSymbol": "",
            "localBusinessPriceRange": "$",
            "localBusinessOpeningHours": [
                {
                    "open": null,
                    "close": null
                },
                {
                    "open": null,
                    "close": null
                },
                {
                    "open": null,
                    "close": null
                },
                {
                    "open": null,
                    "close": null
                },
                {
                    "open": null,
                    "close": null
                },
                {
                    "open": null,
                    "close": null
                },
                {
                    "open": null,
                    "close": null
                }
            ],
            "restaurantServesCuisine": "",
            "restaurantMenuUrl": "",
            "restaurantReservationsUrl": ""
        },
        "creator": {
            "siteType": "Organization",
            "siteSubType": "LocalBusiness",
            "siteSpecificType": "none",
            "computedType": "LocalBusiness",
            "genericName": "",
            "genericAlternateName": "",
            "genericDescription": "",
            "genericUrl": "",
            "genericImage": null,
            "genericImageWidth": "1340",
            "genericImageHeight": "596",
            "genericImageIds": [
                "24"
            ],
            "genericTelephone": "",
            "genericEmail": "",
            "genericStreetAddress": "",
            "genericAddressLocality": "",
            "genericAddressRegion": "",
            "genericPostalCode": "",
            "genericAddressCountry": "",
            "genericGeoLatitude": "",
            "genericGeoLongitude": "",
            "personGender": "Male",
            "personBirthPlace": "",
            "organizationDuns": "",
            "organizationFounder": "",
            "organizationFoundingDate": "",
            "organizationFoundingLocation": "",
            "organizationContactPoints": "",
            "corporationTickerSymbol": "",
            "localBusinessPriceRange": "$",
            "localBusinessOpeningHours": [
                {
                    "open": null,
                    "close": null
                },
                {
                    "open": null,
                    "close": null
                },
                {
                    "open": null,
                    "close": null
                },
                {
                    "open": null,
                    "close": null
                },
                {
                    "open": null,
                    "close": null
                },
                {
                    "open": null,
                    "close": null
                },
                {
                    "open": null,
                    "close": null
                }
            ],
            "restaurantServesCuisine": "",
            "restaurantMenuUrl": "",
            "restaurantReservationsUrl": ""
        },
        "twitterHandle": "",
        "facebookProfileId": "",
        "facebookAppId": "",
        "googleSiteVerification": "",
        "bingSiteVerification": "",
        "pinterestSiteVerification": "",
        "sameAsLinks": {
            "twitter": {
                "siteName": "Twitter",
                "handle": "twitter",
                "url": ""
            },
            "facebook": {
                "siteName": "Facebook",
                "handle": "facebook",
                "url": ""
            },
            "wikipedia": {
                "siteName": "Wikipedia",
                "handle": "wikipedia",
                "url": ""
            },
            "linkedin": {
                "siteName": "LinkedIn",
                "handle": "linkedin",
                "url": ""
            },
            "googleplus": {
                "siteName": "Google+",
                "handle": "googleplus",
                "url": ""
            },
            "youtube": {
                "siteName": "YouTube",
                "handle": "youtube",
                "url": ""
            },
            "instagram": {
                "siteName": "Instagram",
                "handle": "instagram",
                "url": ""
            },
            "pinterest": {
                "siteName": "Pinterest",
                "handle": "pinterest",
                "url": ""
            },
            "github": {
                "siteName": "GitHub",
                "handle": "github",
                "url": ""
            },
            "vimeo": {
                "siteName": "Vimeo",
                "handle": "vimeo",
                "url": ""
            }
        },
        "siteLinksSearchTarget": "",
        "siteLinksQueryInput": "",
        "referrer": "no-referrer-when-downgrade",
        "additionalSitemapUrls": [],
        "additionalSitemapUrlsDateUpdated": null,
        "additionalSitemaps": []
    }
}
```

You can also request individual meta containers.

Title container:

```
/actions/seomatic/meta-container/meta-title-container/?uri=/
```

...will return just the Title container:
```json
{
    "MetaTitleContainer": "<title>[devMode] Craft3 | Homepage</title>"
}
```

Tag container:

```
/actions/seomatic/meta-container/meta-tag-container/?uri=/
```

...will return just the Tag container:
```json
{
     "MetaTagContainer": "<meta name=\"generator\" content=\"SEOmatic\"><meta name=\"referrer\" content=\"no-referrer-when-downgrade\"><meta name=\"robots\" content=\"all\">"
 }
```

Script container:

```
/actions/seomatic/meta-container/meta-script-container/?uri=/
```

...will return just the Script container:
```json
{
    "MetaScriptContainer": ""
}
```

Link container:

```
/actions/seomatic/meta-container/meta-link-container/?uri=/
```

...will return just the Link container:
```json
{
    "MetaLinkContainer": "<link href=\"http://craft3.test/\" rel=\"canonical\"><link type=\"text/plain\" href=\"/humans.txt\" rel=\"author\"><link href=\"http://craft3.test/\" rel=\"alternate\" hreflang=\"es\">"
}
```

JSON-LD container:

```
/actions/seomatic/meta-container/meta-json-ld-container/?uri=/
```

...will return just the JSON-LD container:
```json
{
    "MetaJsonLdContainer": "<script type=\"application/ld+json\">{\"@context\":\"http://schema.org\",\"@type\":\"WebPage\",\"image\":{\"@type\":\"ImageObject\",\"height\":\"804\",\"width\":\"1200\"},\"inLanguage\":\"en-us\",\"mainEntityOfPage\":\"http://craft3.test/\",\"name\":\"Homepage\",\"url\":\"http://craft3.test/\"}</script><script type=\"application/ld+json\">{\"@context\":\"http://schema.org\",\"@type\":\"BreadcrumbList\",\"description\":\"Breadcrumbs list\",\"itemListElement\":[{\"@type\":\"ListItem\",\"item\":{\"@id\":\"http://craft3.test/\",\"name\":\"Homepage\"},\"position\":1}],\"name\":\"Breadcrumbs\"}</script>"
}
```

SiteVars container:

```
/actions/seomatic/meta-container/meta-site-vars-container/?uri=/
```

...will return just the MetaSiteVars container:
```json
{
    "MetaSiteVarsContainer": "{\"siteName\":\"woof\",\"identity\":{\"siteType\":\"Organization\",\"siteSubType\":\"LocalBusiness\",\"siteSpecificType\":\"none\",\"computedType\":\"LocalBusiness\",\"genericName\":\"\",\"genericAlternateName\":\"\",\"genericDescription\":\"\",\"genericUrl\":\"$HOME\",\"genericImage\":null,\"genericImageWidth\":\"229\",\"genericImageHeight\":\"220\",\"genericImageIds\":[\"25\"],\"genericTelephone\":\"\",\"genericEmail\":\"\",\"genericStreetAddress\":\"\",\"genericAddressLocality\":\"\",\"genericAddressRegion\":\"\",\"genericPostalCode\":\"\",\"genericAddressCountry\":\"\",\"genericGeoLatitude\":\"\",\"genericGeoLongitude\":\"\",\"personGender\":\"Male\",\"personBirthPlace\":\"\",\"organizationDuns\":\"\",\"organizationFounder\":\"\",\"organizationFoundingDate\":\"\",\"organizationFoundingLocation\":\"\",\"organizationContactPoints\":\"\",\"corporationTickerSymbol\":\"\",\"localBusinessPriceRange\":\"$\",\"localBusinessOpeningHours\":[{\"open\":null,\"close\":null},{\"open\":null,\"close\":null},{\"open\":null,\"close\":null},{\"open\":null,\"close\":null},{\"open\":null,\"close\":null},{\"open\":null,\"close\":null},{\"open\":null,\"close\":null}],\"restaurantServesCuisine\":\"\",\"restaurantMenuUrl\":\"\",\"restaurantReservationsUrl\":\"\"},\"creator\":{\"siteType\":\"Organization\",\"siteSubType\":\"LocalBusiness\",\"siteSpecificType\":\"none\",\"computedType\":\"LocalBusiness\",\"genericName\":\"\",\"genericAlternateName\":\"\",\"genericDescription\":\"\",\"genericUrl\":\"\",\"genericImage\":null,\"genericImageWidth\":\"1340\",\"genericImageHeight\":\"596\",\"genericImageIds\":[\"24\"],\"genericTelephone\":\"\",\"genericEmail\":\"\",\"genericStreetAddress\":\"\",\"genericAddressLocality\":\"\",\"genericAddressRegion\":\"\",\"genericPostalCode\":\"\",\"genericAddressCountry\":\"\",\"genericGeoLatitude\":\"\",\"genericGeoLongitude\":\"\",\"personGender\":\"Male\",\"personBirthPlace\":\"\",\"organizationDuns\":\"\",\"organizationFounder\":\"\",\"organizationFoundingDate\":\"\",\"organizationFoundingLocation\":\"\",\"organizationContactPoints\":\"\",\"corporationTickerSymbol\":\"\",\"localBusinessPriceRange\":\"$\",\"localBusinessOpeningHours\":[{\"open\":null,\"close\":null},{\"open\":null,\"close\":null},{\"open\":null,\"close\":null},{\"open\":null,\"close\":null},{\"open\":null,\"close\":null},{\"open\":null,\"close\":null},{\"open\":null,\"close\":null}],\"restaurantServesCuisine\":\"\",\"restaurantMenuUrl\":\"\",\"restaurantReservationsUrl\":\"\"},\"twitterHandle\":\"\",\"facebookProfileId\":\"\",\"facebookAppId\":\"\",\"googleSiteVerification\":\"\",\"bingSiteVerification\":\"\",\"pinterestSiteVerification\":\"\",\"sameAsLinks\":{\"twitter\":{\"siteName\":\"Twitter\",\"handle\":\"twitter\",\"url\":\"\"},\"facebook\":{\"siteName\":\"Facebook\",\"handle\":\"facebook\",\"url\":\"\"},\"wikipedia\":{\"siteName\":\"Wikipedia\",\"handle\":\"wikipedia\",\"url\":\"\"},\"linkedin\":{\"siteName\":\"LinkedIn\",\"handle\":\"linkedin\",\"url\":\"\"},\"googleplus\":{\"siteName\":\"Google+\",\"handle\":\"googleplus\",\"url\":\"\"},\"youtube\":{\"siteName\":\"YouTube\",\"handle\":\"youtube\",\"url\":\"\"},\"instagram\":{\"siteName\":\"Instagram\",\"handle\":\"instagram\",\"url\":\"\"},\"pinterest\":{\"siteName\":\"Pinterest\",\"handle\":\"pinterest\",\"url\":\"\"},\"github\":{\"siteName\":\"GitHub\",\"handle\":\"github\",\"url\":\"\"},\"vimeo\":{\"siteName\":\"Vimeo\",\"handle\":\"vimeo\",\"url\":\"\"}},\"siteLinksSearchTarget\":\"\",\"siteLinksQueryInput\":\"\",\"referrer\":\"no-referrer-when-downgrade\",\"additionalSitemapUrls\":[],\"additionalSitemapUrlsDateUpdated\":null,\"additionalSitemaps\":[]}"
}
```

All of the individual container controller API endpoints also accept the `&asArray=true` parameter if you'd like the data in array form.
 
### Schema.org API Endpoints

**N.B.:** Anonymous access to the Schema.org JSON-LD endpoints are disabled by default; you'll need to enable them in SEOmatic → Plugin Settings → Endpoints

To get a key/value array of a given [Schema.org](http://schema.org/docs/full.html) type:

```
/actions/seomatic/json-ld/get-type?schemaType=Article
```

To get a decomposed version of a given [Schema.org](http://schema.org/docs/full.html) type, with the properties grouped by each inherited type:

```
/actions/seomatic/json-ld/get-decomposed-type?schemaType=Article
```

To get a hierarchical array of all of the schema types: 

```
/actions/seomatic/json-ld/get-type-array?path=Article
```

You can narrow this down to a specific sub-type list by passing in a `path` of schema types delimited by a `.`:
```
/actions/seomatic/json-ld/get-type-array?path=CreativeWork.Article
```
...this would output all of the sub-types of `Article`:
```
{
  "AdvertiserContentArticle": "AdvertiserContentArticle",
  "NewsArticle": {
    "AnalysisNewsArticle": "AnalysisNewsArticle",
    "BackgroundNewsArticle": "BackgroundNewsArticle",
    "OpinionNewsArticle": "OpinionNewsArticle",
    "ReportageNewsArticle": "ReportageNewsArticle",
    "ReviewNewsArticle": "ReviewNewsArticle"
  },
  "Report": "Report",
  "SatiricalArticle": "SatiricalArticle",
  "ScholarlyArticle": {
    "MedicalScholarlyArticle": "MedicalScholarlyArticle"
  },
  "SocialMediaPosting": {
    "BlogPosting": {
      "LiveBlogPosting": "LiveBlogPosting"
    },
    "DiscussionForumPosting": "DiscussionForumPosting"
  },
  "TechArticle": {
    "APIReference": "APIReference"
  }
}
```

Brought to you by [nystudio107](https://nystudio107.com/)
