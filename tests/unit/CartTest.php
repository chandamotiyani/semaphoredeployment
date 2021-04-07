<?php
namespace Tests\unit;
​
use Codeception\Test\Unit;
use Craft;
use craft\commerce\controllers\CartController;
use craft\commerce\elements\Variant;
use craft\commerce\Plugin;
use craft\web\Request;
use craftcommercetests\fixtures\SalesFixture;
use UnitTester;
use yii\web\Response;
use yii\base\Event;
use craft\services\Plugins;
​
​
class CartTest extends Unit
{
    /**
     * @var UnitTester
     */
    protected $tester;
​
    /**
     * @var CartController
     */
    protected $cartController;
​
    /**
     * @var Request
     */
    protected $request;
​
    protected $pluginInstance;
​
    /**
     * @return array
     */
    public function _fixtures(): array
    {
        return [
            'sales' => [
                'class' => SalesFixture::class,
            ],
        ];
    }
​
    public function init() {
​
    }
​
    /**
     * @inheritDoc
     */
    protected function _before()
    {
​
        $this->cartController = new CartController('cart', Plugin::getInstance());
        $this->request = Craft::$app->getRequest();
        $this->request->enableCsrfValidation = false;
    }
​
​
​
    public function testGetCart() {
​
        $this->request->headers->set('Accept', 'application/json');
        $return = $this->cartController->runAction('get-cart');
​
        $this->assertInstanceOf(Response::class, $return);
​
        $data = $return->data;
        $this->assertArrayHasKey('cart', $data);
        $this->assertArrayHasKey('total', $data['cart']);
        $this->assertEquals(0, $data['cart']['total']);
    }
​
​
}