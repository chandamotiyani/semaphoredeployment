<?php


namespace modules\Events\Fields;


use craft\base\ElementInterface;
use craft\fields\BaseRelationField;
use \Craft;

class Schedule extends BaseRelationField {
	public static function displayName(): string {
		return 'Schedule';
	}

	public static function pluralDisplayName(): string {
		return 'Schedule';
	}

	protected static function elementType(): string {
		return \modules\Events\Elements\Schedule::class;
	}

	public static function defaultSelectionLabel(): string {
		return 'Add a Schedule';
	}

	public function getInputHtml($value, ElementInterface $element = null): string
	{
		return Craft::$app->getView()->renderTemplate('events/fields/schedule-field',['value'=>$value, 'element'=>$element]);
	}

}