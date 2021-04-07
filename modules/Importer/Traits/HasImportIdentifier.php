<?php


namespace modules\Importer\Traits;


use craft\console\Application as ConsoleApplication;
use modules\Importer\ImporterModule;
use yii\helpers\Console;

trait HasImportIdentifier {
	public static function getImporterIdentifier(){
		return static::$importerIdentifier;

	}
}