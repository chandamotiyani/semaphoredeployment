<?php


namespace modules\Events\Elements\Traits;


trait Statuses {

	private static $STATUS_PAST = 'past';
	private static $STATUS_UPCOMING = 'upcoming';
	private static $STATUS_DISABLED = 'disabled';

	public static function hasStatuses(): bool
	{
		return true;
	}


	public static function statuses(): array
	{
		return [
			'past' => ['label' => 'Past', 'color' => 'F2842D'],
			'upcoming' => ['label' => 'Upcoming', 'color' => '27AE60'],
			'disabled' => ['label' => 'Disabled', 'color' => 'AAAAAA'],
		];
	}

	public function getStatus()
	{
		if($this->getStartDateTime() > new \DateTime()){
			return self::$STATUS_UPCOMING;
		}
		if($this->getStartDateTime() < new \DateTime()){
			return self::$STATUS_PAST;
		}
		return self::$STATUS_DISABLED;
	}

	protected function statusCondition(string $status)
	{
		switch ($status) {
			case self::$STATUS_PAST:
				return [self::$STATUS_PAST => true];
			case self::$STATUS_UPCOMING:
				return [self::$STATUS_UPCOMING => true];
			default:
				return [self::$STATUS_DISABLED=>true];
		}
	}

}