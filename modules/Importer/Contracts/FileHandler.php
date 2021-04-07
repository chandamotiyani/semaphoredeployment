<?php


namespace modules\Importer\Contracts;


interface FileHandler {
	public function getConfig();       //TODO: return types.
	public function getAdapter();       //TODO: return types.
	public function getFileSystem();       //TODO: return types.
}