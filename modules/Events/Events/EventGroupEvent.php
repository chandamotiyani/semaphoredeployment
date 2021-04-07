<?php
/**
 * @link https://craftcms.com/
 * @copyright Copyright (c) Pixel & Tonic, Inc.
 * @license https://craftcms.github.io/license/
 */

namespace modules\Events\Events;

use modules\Events\Models\EventGroupModel;
use yii\base\Event;


class EventGroupEvent extends Event
{
	// Properties
	// =========================================================================

	/**
	 * @var EventGroupModel|null The event group model associated with the event.
	 */
	public $eventGroup;

	/**
	 * @var bool Whether the event group is brand new
	 */
	public $isNew = false;
}
