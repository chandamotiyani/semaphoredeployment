<?php

namespace modules\Importer\Traits;

use Aws\S3\S3Client;
use League\Flysystem\Adapter\Local;
use League\Flysystem\AwsS3v3\AwsS3Adapter;
use League\Flysystem\Filesystem;
use modules\Importer\Contracts\FileHandler;

trait HandlesFiles {

	private $file = [];
	private $adapter;


	public function copyFile(FileHandler $fileHandler, FileHandler $fileHandler2){

		$this->rotate($fileHandler2);
		// and download the new file
		$fileHandler2->getFileSystem()->writeStream($this->getPath(), $fileHandler->getFileSystem()->readStream($this->getPath()));
		return true;
	}

	/**
	 * rotates any existing files that might exist in the folder.
	 * items over ($max) are deleted.
	 * @param FileHandler $fileHandler
	 * @param int $max
	 */
	public function rotate(FileHandler $fileHandler, int $max = 5){
		$path = $this->getPath();
		$filename = pathinfo($path, PATHINFO_FILENAME);
		if($fileHandler->getAdapter()->has($path)){
			//all files in the folder:
			$c = $fileHandler->getFileSystem()->listContents('', false);

			//all the existing versions of this file:
			$files = array_filter($c, function($item) use($path, $filename){
				preg_match(('/'.$filename.'$/'), $item['filename'], $matches);
				if(count($matches)){
					return true;
				}
			});

			//if there's more than $max, we need to delete the oldest one
			if(count($files) > $max){
				//sort the files by name
				uasort($files, function($item1, $item2){
					return ($item1['filename'] < $item2['filename']) ? -1 : 1;
				});
				//and delete the oldest one:
				$files = array_values($files);
				$toDelete = $files[0];

				$fileHandler->getFileSystem()->delete($toDelete['path']);
			}

			//rename the previous one to it's last update time
			$datetime = $fileHandler->getFileSystem()->getTimestamp($path);
			$newPath = "$datetime.".$this->getPath();
			$fileHandler->getFileSystem()->rename($path,$newPath);
		}
	}

}