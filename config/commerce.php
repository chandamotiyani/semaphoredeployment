<?php
// see craftcms/commerce/docs/configuration.md
return [
	'*' => [
		'requireBillingAddressAtCheckout'=>true,
		'requireShippingAddressAtCheckout'=>true,
		'requireShippingMethodSelectionAtCheckout'=>true,
        //These are the membership groups that the permissioning system needs to be concerened with.
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
            'Flat rate Australia Wide'=>[
                'phonetic'=>'CDSFRT',
                'itemsPerCase'=>1,
                'unitOfMeasure'=>'EA'
            ]
        ],
        //if stock level dips below this value, we send emails to yalumba to warn of low stock.
        'lowStockThreshold'=>10,

        'excludedPostcodes' => ['4605','4713','4803','4816','4825','4830','4871','4874','4876','4892','4895','6431','6435','6436','6437','6438','6440','6635','6638','6639','6640','6642','6642','6710','6713','6714','6716','6718','6721','6722','6751','6754','6798','6799','6770','6765','6718','6723','6725','6726','6728','6731','6733','6740','6743','6753','6761','6765','6770','6430','5723','5734','5680','5690','0822','0861','0862','0870','0871','0872','0847','0850','0851','0852','0853','0880','0881','0886','0860'],
	],

    'updateCartSearchIndexes' => false,
    'pdfAllowRemoteImages' => true,

];