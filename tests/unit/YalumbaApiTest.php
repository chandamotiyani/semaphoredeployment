<?php

namespace Tests\unit;

use Codeception\Test\Unit;

use craft\commerce\elements\Order;
use craft\commerce\Plugin as Commerce;
use craft\elements\User;
use modules\Events\EventsModule;
use modules\Yalumba\Jobs\SendCreateMemberJob;
use modules\Memberships\MembershipModule;
use modules\Yalumba\YalumbaApiModule;
use UnitTester;
use \Craft;

class YalumbaApiTest extends Unit
{
	/**
	 * @var UnitTester
	 */
	protected $tester;

	protected $module;


	public function _before(){
		$this->module = YalumbaApiModule::getInstance();

	}

    /**
     * provides completed orders from the database
     * @return array
     */
    public static function completedOrdersProvider(){
        //TODO: this doesn't seem to work - maybe we should be mocking these?
        //the tests themselves pass when passed a correct order object.
        $orders = Order::find()->isCompleted(1)->orderBy('dateOrdered')->limit(3);
        return array(
            array(1),
            array(3),
            array(5)
        );
        return [
            [$orders[0]],
            [$orders[1]],
            [$orders[2]],
        ];
    }

    /**
     * @dataProvider completedOrdersProvider
     */
    public function testGetLineItems($order){
        $lineItemOptions = $this->module->api->getLineItemOptions($order);
    }

    /**
     * get the shipping
     * @param $order
     * @dataProvider completedOrdersProvider
     */
	public function testGetShippingPhoneticArray($order){
	    $shippingProductArray = $this->module->api->getShippingProductArray($order);
	    $this->assertArrayHasKey('phonetic', $shippingProductArray);
	    $this->assertIsString($shippingProductArray['phonetic']);
        $this->assertArrayHasKey('unitOfMeasure', $shippingProductArray);
        $this->assertArrayHasKey('itemsPerCase', $shippingProductArray);
    }

    /**
     * @dataProvider completedOrdersProvider
     */
    public function testGetShippingLineItem($order){
        $shippingProductArray = $this->module->api->getShippingProductArray($order);
        $lineItem = $this->module->api->getShippingLineItem($order, $shippingProductArray);
        $this->assertIsArray($lineItem);
        $this->assertArrayHasKey('unitOfMeasure',$lineItem);
        $this->assertArrayHasKey('qtyOrdered',$lineItem);
        $this->assertEquals(1, $lineItem['qtyOrdered']);
        $this->assertArrayHasKey('phonetic',$lineItem);
        $this->assertArrayHasKey('itemsPerCase',$lineItem);
    }

    /**
     * @dataProvider completedOrdersProvider
     */
    public function testGetLineItemOptions($order){
        $options = $this->module->api->getLineItemOptions($order);
        $this->assertIsArray($options);
        foreach($options as $option){
            $this->assertArrayHasKey('unitOfMeasure',$option);
            $this->assertArrayHasKey('qtyOrdered',$option);
            $this->assertArrayHasKey('phonetic', $option);
            $this->assertArrayHasKey('extendedPrice', $option);
            $this->assertArrayHasKey('itemsPerCase', $option);
            $this->assertIsString($option['unitOfMeasure']);
            $this->assertIsInt($option['qtyOrdered']);
            $this->assertIsString($option['phonetic']);
            $this->assertIsFloat($option['extendedPrice']);
            $this->assertIsInt($option['itemsPerCase']);
        }
    }
}
