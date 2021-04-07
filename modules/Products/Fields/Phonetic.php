<?php


namespace modules\Products\Fields;


use craft\base\ElementInterface;
use craft\base\Field;
use \Craft;
use craft\helpers\Json;
use modules\Products\PhoneticFieldAssetBundle;
use modules\Orders\Models\Records\ProductRecord;


class Phonetic extends Field {
	public static function displayName(): string {
		return 'Phonetic';
	}


	public static function defaultSelectionLabel(): string {
		return 'Add a phonetic';
	}

	public function getInputHtml($value, ElementInterface $element = null): string
	{

		$id = Craft::$app->getView()->formatInputId($this->handle);

		Craft::$app->getView()->registerAssetBundle(PhoneticFieldAssetBundle::class);
		Craft::$app->getView()->registerJs('new Craft.Products.Phonetic("'.Craft::$app->getView()->namespaceInputId($id).'");');
		//TODO: grab the products.
		//val=>lab
		$products = ProductRecord::find()->orderBy('phonetic')->all();
		$productsOptions = [];
		foreach($products as $product){
			$productsOptions[$product->phonetic] = "$product->phonetic - $product->name";
		}

		return Craft::$app->getView()->renderTemplate('products/_phonetic',[
			'value'=>$value,
			'element' => $element,
			'name'=>$this->handle,
			'options'=>$productsOptions,
			'field'=>$this
		]);

	}
}