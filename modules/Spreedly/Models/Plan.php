<?php


namespace modules\Spreedly\Models;


use craft\commerce\base\Plan as BasePlan;
use craft\commerce\base\PlanInterface;

class Plan extends BasePlan{

	/**
	 * Returns whether it's possible to switch to this plan from a different plan.
	 *
	 * @param PlanInterface $currentPlant
	 *
	 * @return bool
	 */
	public function canSwitchFrom( PlanInterface $currentPlant ): bool {
		return true;
	}
}