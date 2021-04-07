<?php


namespace modules\Products;


use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;
use yii\web\JqueryAsset;

class PhoneticFieldAssetBundle extends AssetBundle {
	public function init()
	{
		// define the path that your publishable resources live
		$this->sourcePath = '@modules/Products/resources';

		// define the dependencies
		$this->depends = [
			CpAsset::class,
			JqueryAsset::class,
		];

		// define the relative path to CSS/JS files that should be registered with the page
		// when this asset bundle is registered
		$this->js = [
			'Phonetic.js',
            'Selectize.js'
		];

		$this->css = [
		    'Selectize.css'
			//'file.css'
		];

		parent::init();
	}
}