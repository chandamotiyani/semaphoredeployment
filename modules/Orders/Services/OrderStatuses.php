<?php


namespace modules\Orders\Services;

use craft\base\Component;
use craft\commerce\Plugin as Commerce;
use modules\Orders\Models\LineItemModel;
use modules\Orders\Models\Records\LineItemRecord;
use modules\Orders\Models\OrderModel;
use modules\Orders\Models\Records\OrderRecord as OrderRecord;

class OrderStatuses extends Component {
    /* Chanda - we can change status by Id not by handle so have added the ids of respective statuses */
    private $status_map = [
        'default'=>[
            'yalumba_status'=>'',
            'craft_handle'=>'new',
            'craftStatusId' => 2,
        ],
        [
            'yalumba_status'=>'Completed',
            'craft_handle'=>'completed',
            'craftStatusId' => 4,
        ],
        [
            'yalumba_status'=>'Cancelled',
            'craft_handle'=>'cancelled',
            'craftStatusId' => 5,
        ],
        [
            'yalumba_status'=>'Completed/Cancelled',
            'craft_handle'=>'cancelled',
            'craftStatusId' => 5,
        ],
        [
            'yalumba_status'=>'Click View Order',
            'craft_handle'=>'confirmed',
            'craftStatusId' => 3,
        ],
        [
            'yalumba_status'=>'Picked',
            'craft_handle'=>'confirmed',
            'craftStatusId' => 3,
        ],
        [
            'yalumba_status'=>'Invoice Printed',
            'craft_handle'=>'shipped',
            'craftStatusId' => 1,
        ],
        [
            'yalumba_status'=>'Shipped',
            'craft_handle'=>'shipped',
            'craftStatusId' => 1,
        ],
        [
            'yalumba_status'=>'Ship Confirm',
            'craft_handle'=>'shipped',
            'craftStatusId' => 1,
        ],
        [
            'yalumba_status'=>'Ordered',
            'craft_handle'=>'confirmed',
            'craftStatusId' => 3,
        ],
        [
            'yalumba_status'=>'On Back Order',
            'craft_handle'=>'confirmed',
            'craftStatusId' => 3,
        ]

    ];

    /**
     * Orders constructor.
     */
    public function __construct() {

    }

    public function getCraftStatusFromYalumbaStatus(string $yalumbaStatus){
        $key = array_search($yalumbaStatus, array_column($this->status_map, 'yalumba_status'));
        if($key === false){
            return $this->status_map['default']['craft_handle'];
        }
        return Commerce::getInstance()->orderStatuses->getOrderStatusByHandle($this->status_map[$key - 1]['craft_handle']);
    }

    public function getYalumbaStatusFromCraftStatus(string $handle){
        $key = array_search($handle, array_column($this->status_map, 'craft_handle'));
        if($key === false){
            return $this->status_map['default']['yalumba_status'];
        }
        return $this->status_map[$key]['yalumba_status'];
    }

    /*
     * Chanda - this function will return the orderStatusId by Yalumba Status
     */

    public function getCraftStatusIdFromYalumbaStatus(string $yalumbaStatus){
        $key = array_search($yalumbaStatus, array_column($this->status_map, 'yalumba_status'));
        if($key === false){
            return $this->status_map['default']['craftStatusId'];
        }
        return $this->status_map[$key - 1]['craftStatusId'];
    }
}
