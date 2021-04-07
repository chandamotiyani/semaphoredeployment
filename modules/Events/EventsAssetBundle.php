<?php


namespace modules\Events;


use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;
use yii\web\JqueryAsset;

class EventsAssetBundle extends AssetBundle {
	public function init()
	{
		// define the path that your publishable resources live
		$this->sourcePath = '@modules/Events/resources';

		// define the dependencies
		$this->depends = [
			CpAsset::class,
			JqueryAsset::class,
		];

		// define the relative path to CSS/JS files that should be registered with the page
		// when this asset bundle is registered
		$this->js = [
			'Events.js',
			'EventsEventIndex.js'
		];

//		$this->css = [
//			'styles.css',
//		];

		parent::init();
	}
}