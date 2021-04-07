<?php


namespace modules\Importer\Contracts;


interface Importer {
	public static function getImporterIdentifier();
	public function import();
	public function log($message, $category);
}