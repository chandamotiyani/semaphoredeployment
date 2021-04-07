<?php


namespace modules\Products;


use craft\commerce\elements\Product;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterTemplateRootsEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\services\Fields;
use craft\web\UrlManager;
use craft\web\View;
use modules\Products\Fields\Disabled;
use modules\Products\Fields\Phonetic;
use yii\base\Event;
use yii\base\ModelEvent;
use yii\base\Module;

class ProductsModule extends Module {

	/**
	 * ImporterModule constructor.
	 *
	 * @param $id
	 * @param null $parent
	 * @param array $config
	 */
	public function __construct($id, $parent = null, array $config = []) {
		\Craft::setAlias('@modules/Products', $this->getBasePath());
		$this->controllerNamespace = 'modules\Products\Controllers';
		// Set this as the global instance of this module class
		static::setInstance($this);
		parent::__construct($id, $parent, $config);
	}

	public function init(){
		parent::init();

		$this->setComponents([

		]);

		// Base template directory
		Event::on(View::class, View::EVENT_REGISTER_CP_TEMPLATE_ROOTS, function (RegisterTemplateRootsEvent $e) {
			if (is_dir($baseDir = $this->getBasePath().DIRECTORY_SEPARATOR.'templates')) {
				$e->roots[$this->id] = $baseDir;
			}
		});
// add a couple of custom fields
		Event::on(Fields::class, Fields::EVENT_REGISTER_FIELD_TYPES, function(RegisterComponentTypesEvent $event) {
			$event->types[] = Phonetic::class;
            $event->types[] = Disabled::class;
		});

        // we need to check the phonetic field is filled in.. if it isn't, we need to set the product as unavailable
        // for purchase.. except if it's a Wine Packs and Gifts - it's still optional then.
        Event::on(Product::class, Product::EVENT_BEFORE_VALIDATE, function(ModelEvent $event){
            $product = $event->sender;
            if($product->type->handle != 'gifts'){
                if($product->phonetic === ''){
                    $product->availableForPurchase = false;
                }
                foreach($product->variants as $variant){
                    if($variant->phonetic === ''){
                        //$variant->enabled = false;
                        $product->availableForPurchase = false;
                    }
                }
            }
        });

		// Register our CP routes
		Event::on(
			UrlManager::class,
			UrlManager::EVENT_REGISTER_CP_URL_RULES,
			function (RegisterUrlRulesEvent $event) {
				$event->rules = array_merge($event->rules,$this->cpRoutes());
			}
		);

		// Chanda - Sync product prices with Vend
        Event::on(Product::class, Product::EVENT_AFTER_SAVE, function (ModelEvent $event) {
            if($event->isNew) {
                // Chanda - call VEND job here to sync prices
                /*$params = ['userId' => $event->sender->id, 'userUid'=>$event->sender->uid];
                Craft::$app->queue->push(new YalumbaSendCreateMemberJob($params));
                Craft::$app->queue->push(new VendSendCreateMemberJob($params));*/
            }
        });

	}


	public function cpRoutes() {
		return [
			'/products' => 'products/products/index',
            '/products/export' => 'products/products/export',
			'/products/<productId:\d+>' => 'products/products/index'
		];
	}

}