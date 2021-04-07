<?php
/**
 * @link https://craftcms.com/
 * @copyright Copyright (c) Pixel & Tonic, Inc.
 * @license https://craftcms.github.io/license/
 */

namespace modules\Events\Events;

use modules\Events\Models\EventGroupModel;
use modules\Events\Models\ScheduleGroupModel;
use yii\base\Event;


class ScheduleGroupEvent extends Event
{
	// Properties
	// =========================================================================

	/**
	 * @var ScheduleGroupModel|null The event group model associated with the event.
	 */
	public $scheduleGroup;

	/**
	 * @var bool Whether the event group is brand new
	 */
	public $isNew = false;
}
