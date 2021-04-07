<?php


namespace modules\Events\Elements\Traits;


trait Routing {

	public function getUriFormat() {
		return 'events/{slug}';
	}

	/**
	 * @inheritDoc
	 */
	public static function hasUris(): bool
	{
		return true;
	}

	protected function route()
	{
		return [
			'templates/render', [
				'template' => 'events/_event',
				'variables' => [
					'event' => $this,
				]
			]
		];
	}
}