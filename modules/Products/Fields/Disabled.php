<?php


namespace modules\Products\Fields;


use craft\base\ElementInterface;
use craft\base\Field;
use \Craft;
use craft\helpers\Json;
use modules\Products\PhoneticFieldAssetBundle;
use modules\Orders\Models\Records\ProductRecord;


class Disabled extends Field {
	public static function displayName(): string {
		return 'Disabled';
	}


	public static function defaultSelectionLabel(): string {
		return 'Disabled field';
	}

	public function getInputHtml($value, ElementInterface $element = null): string
	{
		return Craft::$app->getView()->renderTemplate('products/_disabled',[
			'value'=>$value,
			'element' => $element,
			'name'=>$this->handle,
			'field'=>$this
		]);
	}
}