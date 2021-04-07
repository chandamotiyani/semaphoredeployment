<?php

return [
    '*' => [
        'pluginName' => 'Wishlist',
        'allowDuplicates' => false,
        'manageDisabledLists' => true,
        'mergeLastListOnLogin' => true,
        'purgeInactiveLists' => true,
        'purgeInactiveListsDuration' => 'P3M', // 3 months
        'purgeInactiveGuestListsDuration' => 'P1D', // 1 day
        'purgeEmptyListsOnly' => true,
        'purgeEmptyGuestListsOnly' => false,
        'updateItemSearchIndexes' => false,
        'updateListSearchIndexes' => false,
    ]
];
