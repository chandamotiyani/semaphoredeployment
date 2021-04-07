<?php
// see craftcms/commerce/docs/configuration.md
return [
	'*' => [
		'requireBillingAddressAtCheckout'=>true,
		'requireShippingAddressAtCheckout'=>true,
		'requireShippingMethodSelectionAtCheckout'=>true,
		'member-discount-types'  => [
			'Insider Discount' => 4,
			'Adventurer Discount' => 5,
			'Dabbler Discount' => 6,
		],
		'wine-of-the-month-sale-id' => 1,

        //shipping methods get sent to yalumba with phonetics, pic and uom
        // here we are attaching the relevant ones to the shipping method(s)
        // that the site uses. ie people use flatRateAustraliaWide, the
        // YalumbaAPI module attaches the phonetic to each order using this
        // shipping method.
        'shippingMethods'=>[
            'flatRateAustraliaWide'=>[
                'phonetic'=>'CDSFRT',
                'itemsPerCase'=>1,
                'unitOfMeasure'=>'EA'
            ]
        ]
	],

    'updateCartSearchIndexes' => false,
];