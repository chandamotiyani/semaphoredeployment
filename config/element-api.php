<?php

use craft\elements\Entry;
use craft\commerce\elements\Product;
use modules\Events\Elements\Event;
use modules\Memberships\MembershipPermissionBehavior;


use craft\base\ElementInterface;
use craft\elementapi\resources\ElementResource;
use craft\elements\db\ElementQueryInterface;
use modules\TwigExtensions\extensions\getProductSearchKeywords;

class CriteriaResource extends ElementResource
{
    protected function getElementQuery(): ElementQueryInterface
    {
        $query = parent::getElementQuery();
        $query->withPermission();
        return $query;
    }
}

return [
    'endpoints' => [
        'search.entries' => function () {

            // settings
            $section_handle = ['backVintage', 'blogPosts', 'contact',
                'contactThankYouPage', 'events', 'home', 'insiderRegistrationForm',
                'memberships', 'pages', 'signInForm', 'wineClubPortalMyAccount', 'wineRoomSignup', 'tastings',
            'tours','wishlist'];

            $phrase = Craft::$app->request->getParam('phrase');
            $limit = Craft::$app->request->getParam('limit');
            $state = Craft::$app->request->getParam('state');
            $criteria = [
                'limit' => $limit,
                'search' => 'title:*'.$phrase.'* OR fullTitle:*'.$phrase.'*',
                'section' => $section_handle
            ];

            return [
                'class' => CriteriaResource::class,
                'elementType' => Entry::class,
                'criteria' => $criteria,
                'paginate' => false,
                'transformer' => function (Entry $entry) use ($state) {
                    return [
                        'title' => $entry->title,
                        'fullTitle' => $entry->fullTitle,
                        'url' => $entry->url,
                        'sUrl' => "/search?state=" . $state . "&search=" . $entry->title,
                    ];
                },

            ];
        },
        'search.products' => function () {

            // settings
            $phrase = Craft::$app->request->getParam('phrase');
            $limit = Craft::$app->request->getParam('limit');
            $state = Craft::$app->request->getParam('state');
            /* Chanda - commenting following code because it causing the results load slower and it was used to make special releases such as exclusive wines to be listed if searched under recommended search */
            //$get_keyword = new getProductSearchKeywords();
            //$getProductSearchKeywords = $get_keyword->getProductSearchKeywords($phrase);
            $criteria = [
                'search' => 'title:' . $phrase,
                'limit' => $limit,
                'orderBy' => 'defaultPrice DESC'
            ];

            return [
                'class' => CriteriaResource::class,
                'elementType' => Product::class,
                'criteria' => $criteria,
                'paginate' => false,
                'transformer' => function (Product $entry) use ($state) {

                    return [
                        'title' => $entry->title,
                        'url' => $entry->url,
                        'sUrl' => "/search?state=" . $state . "&search=" . $entry->title,
                    ];
                },


            ];
        },

        'search.events' => function () {

            // settings
            $phrase = Craft::$app->request->getParam('phrase');
            $limit = Craft::$app->request->getParam('limit');
            $state = Craft::$app->request->getParam('state');
            $criteria = [
                'search' => 'title:' . $phrase,
                'limit' => $limit,
                'orderBy' => 'price DESC'
            ];

            return [
                'class' => CriteriaResource::class,
                'elementType' => Event::class,
                'criteria' => $criteria,
                'paginate' => false,
                'transformer' => function (Event $entry) use ($state) {

                    return [
                        'title' => $entry->title,
                        'url' => $entry->url,
                        'sUrl' => "/search?state=" . $state . "&search=" . $entry->title,

                    ];
                },
            ];
        },

        'search.suggestion.keywords' => function () {

            // settings
            $phrase = Craft::$app->request->getParam('phrase');
            $limit = Craft::$app->request->getParam('limit');
            $state = Craft::$app->request->getParam('state');
            $section_handle = ['searchSuggestions'];
            if ($phrase == 'default') {
                $criteria = [
                    'isDefault' => 1,
                    'section' => $section_handle
                ];
            } else {
                $criteria = [
                    'limit' => $limit,
                    'search' => $phrase,
                    'section' => $section_handle
                ];
            }

            return [
                'elementType' => Entry::class,
                'criteria' => $criteria,
                'paginate' => false,
                'transformer' => function (Entry $entry) use ($state) {
                    return [
                        'title' => ucfirst($entry->title),
                        'url' => $entry->url,
                        'sUrl' => $entry->searchSuggestionUrl ? $entry->searchSuggestionUrl : "/search?state=" . $state . "&search=" . $entry->title,
                    ];
                },

            ];
        },
        'search.backVintages' => function () {

            // settings
            $phrase = Craft::$app->request->getParam('phrase');
            $limit = Craft::$app->request->getParam('limit');
            $section_handle = 'backVintages';
            $criteria = [
                'section' => $section_handle,
                'search' => 'fullTitle:' . $phrase,
                'limit' => $limit,
            ];

            return [
                'class' => CriteriaResource::class,
                'elementType' => Entry::class,
                'criteria' => $criteria,
                'paginate' => false,
                'transformer' => function (Entry $entry)  {
                    if($entry->fullTitle) {
                        return [
                            'title' => ucfirst($entry->fullTitle),
                            'url' => $entry->url.'/#main'
                        ];
                    }
                },

            ];
        },
    ]
];