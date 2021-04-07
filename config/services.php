<?php
return [

	// All environments
	'*' => [
		'yalumba-data-feed'=>[
			'access-key-id'=>getenv('S3_DATA_FEED_ACCESS_KEY'),
			'access-secret'=>getenv('S3_DATA_FEED_ACCESS_SECRET'),
			'bucket'=>getenv('S3_DATA_FEED_BUCKET'),
			'region'=>getenv('S3_DATA_FEED_REGION'),
			'version'=>getenv('S3_DATA_FEED_VERSION'),
		],
		'local'=>[
			'root'=>CRAFT_BASE_PATH.'/storage/importer'
		],
		'yalumba-api'=>[
			'subscription-key'=>getenv('YALUMBA_API_SUBSCRIPTION_KEY'),
            'host'=>getenv('YALUMBA_API_HOST')
		],
		'vend-api'=>[
			'client-key'=>getenv('VEND_API_CLIENT_KEY'),
			'client-secret'=>getenv('VEND_API_CLIENT_SECRET'),
			'personal-token'=>getenv('VEND_API_PERSONAL_TOKEN'),
			'domain-prefix'=>getenv('VEND_DOMAIN_PREFIX'),
			'customer-groups'=>[
                'deleted'=>getenv('VEND_DELETED_CUSTOMER_GROUP'),
				'insider'=>getenv('VEND_INSIDER_CUSTOMER_GROUP'),
				'dabbler'=>getenv('VEND_DABBLER_CUSTOMER_GROUP'),
				'adventurer'=>getenv('VEND_ADVENTURER_CUSTOMER_GROUP'),
			]
		],
		'rezdy-api'=>[
			'client-key'=>getenv('REZDY_CLIENT_KEY'),
			'client-secret'=>getenv('REZDY_CLIENT_SECRET'),
			'api-key'=>getenv('REZDY_API_KEY'),
            'url'=>getenv('REZDY_URL'),
		]
	]
];
