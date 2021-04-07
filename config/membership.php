<?php

return [
	'*' => [

	    // in order for us to sync membership levels with the yalumba api, we need to know which user groups
        // should be synced with yalumba.. (admin user group for example, doesn't need to be sent to yalumba)
        "yalumba-membership"=>[
            "adventurer",
            "dabbler",
            "insider",
        ]
	]
];