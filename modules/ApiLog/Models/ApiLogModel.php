<?php


namespace modules\ApiLog\Models;


use craft\base\Model;
use craft\commerce\Plugin as Commerce;
use craft\commerce\records\Customer;
use modules\Orders\OrdersModule;

class ApiLogModel extends Model {

	public $name;
	public $id;
	public $dateUpdated;
	public $dateCreated;
	public $uid;
    public $dateDeleted;
    public $data;
    public $elementId;

    public $_element;
    public $_log;


	public function getElement(){
		if(null === $this->_element){
			$this->_element = \Craft::$app->elements->getElementById($this->id);
		}

		return $this->_element;
	}

}