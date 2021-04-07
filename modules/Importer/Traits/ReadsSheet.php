<?php

namespace modules\Importer\Importers;

use modules\Importer\Components\ChunkReadFilter;
use modules\Importer\Components\LocalFileHandler;
use modules\Importer\Contracts\FileHandler;
use modules\Importer\Traits\HandlesFiles;
use PhpOffice\PhpSpreadsheet\IOFactory;

trait ReadsSheet {
	use HandlesFiles;

	private $fullLocalPath;

	private function readSheet(FileHandler $file) {
		// PHPSpreadsheet requires the actual physical file to be on disk (no
		// loading in via s3 or whatever). So we need to shove the file onto
		// local disk then read it from there.

		$localFileHandler = new LocalFileHandler( \Craft::$app->getConfig()->getConfigFromFile( 'services' )['local'] );

		$this->copyFile( $file, $localFileHandler );
		$this->fullLocalPath = $localFileHandler->getFilePath( $this->getPath() );
		unset($localFileHandler);
		return $this;
	}

	private function processRows($rowsToSkip, $function){
		// the file is now on local disk
		// we do some fancy chunk handling incase the file is b-i-g
		$reader = IOFactory::createReaderForFile($this->fullLocalPath);
		$initialSheet = $reader->load($this->fullLocalPath);
		$numberOfRows = $initialSheet->getActiveSheet()->getHighestDataRow();
		$chunkSize = 20;
		$chunkFilter = new ChunkReadFilter();
		$reader->setReadFilter($chunkFilter);

		// Loop to read our worksheet in "chunk size" blocks
		for ($startRow = $rowsToSkip; $startRow <= $numberOfRows; $startRow += $chunkSize) {
			// Tell the Read Filter which rows we want this iteration
			$chunkFilter->setRows($startRow,$chunkSize);
			// Load only the rows that match our filter
			$spreadsheet = $reader->load($this->fullLocalPath);
			//run the callback
			$function($spreadsheet);
			unset($spreadsheet);
		}
		unset($chunkFilter);
	}
}