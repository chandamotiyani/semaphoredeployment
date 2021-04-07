<?php

namespace modules\Importer\Components;

use Aws\S3\S3Client;
use League\Flysystem\Adapter\Local;
use League\Flysystem\AwsS3v3\AwsS3Adapter;
use League\Flysystem\Filesystem;
use modules\Importer\Contracts\FileHandler;

class LocalFileHandler implements FileHandler {

	private $adapter;
	private $config;
	private $fileSystem;
	private $configKey;

	/**
	 * LocalFileHandler constructor.
	 *
	 * @param $config
	 */
	public function __construct( $config ) {
		$this->config = $config;
	}

	public function getConfig(){
		return $this->config;
	}


	public function getAdapter(){
		if($this->adapter){
			return $this->adapter;
		}
		else{
			return new Local($this->getConfig()['root']);
		}
	}

	public function getFileSystem(){
		if($this->fileSystem){
			return $this->fileSystem;
		}
		else{
			return new Filesystem($this->getAdapter());
		}
	}

	public function getFilePath($path){
		return $this->getAdapter()->getPathPrefix().$path;
	}

}