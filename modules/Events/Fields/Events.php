<?php


namespace modules\Events\Fields;


use craft\fields\BaseRelationField;
use modules\Events\Elements\Event;

class Events extends BaseRelationField {
	public static function displayName(): string {
		return 'Events';
	}

	protected static function elementType(): string {
		return Event::class;
	}

	public static function defaultSelectionLabel(): string {
		return 'Add an event';
	}
}