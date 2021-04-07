<?php


namespace modules\Importer\Traits;


use craft\console\Application as ConsoleApplication;
use modules\Importer\ImporterModule;
use yii\helpers\Console;

trait HasLog {
	public function log($message, $category = NULL){
		if(!$category){
			$category = get_class($this);
		}
		if (\Craft::$app instanceof ConsoleApplication) {
			Console::stdout($category, Console::FG_GREEN);
			Console::stdout(" $message".PHP_EOL, Console::FG_YELLOW);
		}
		ImporterModule::getInstance()->importer->log("$message", "$category");
	}
}