<?php


namespace modules\Rezdy\Fields;


use craft\base\ElementInterface;
use craft\base\Field;
use \Craft;
use modules\Orders\Models\ProductModel;
use modules\Orders\Models\Records\ProductRecord;
use modules\Rezdy\Models\Records\APIEventRecord;


class RezdyId extends Field {
	public static function displayName(): string {
		return 'Rezdy Product ID';
	}


	public static function defaultSelectionLabel(): string {
		return 'Add a Rezdy Product ID';
	}

	public function getInputHtml($value, ElementInterface $element = null): string
	{
		$events = APIEventRecord::find()->all();
		$eventsOptions = [];
		foreach($events as $event){
			$eventsOptions[$event->identifier] = "$event->identifier $event->name";
		}

		return Craft::$app->getView()->renderTemplate('rezdy/fields/_rezdy_id',[
			'value'=>$value,
			'element' => $element,
			'options'=>$eventsOptions,
			'field' => $this,
		]);
	}
}