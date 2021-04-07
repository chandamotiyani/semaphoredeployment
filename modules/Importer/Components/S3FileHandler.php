<?php

namespace modules\Importer\Components;

use Aws\S3\S3Client;
use League\Flysystem\AwsS3v3\AwsS3Adapter;
use League\Flysystem\Filesystem;
use modules\Importer\Contracts\FileHandler;

class S3FileHandler implements FileHandler {

	private $import;
	private $adapter;
	private $client;
	private $config;
	private $fileSystem;
	private $configKey;

	/**
	 * S3FileHandler constructor.
	 *
	 * @param $config
	 */
	public function __construct( $config ) {
		$this->config = $config;
	}

	public function getConfig(){
		return $this->config;
	}

	public function getClient(){
		if($this->client){
			return $this->client;
		}
		else{
			return new S3Client([
				'credentials' => [
					'key'    => $this->getConfig()['access-key-id'],
					'secret' => $this->getConfig()['access-secret']
				],
				'region' => $this->getConfig()['region'],
				'version'=>$this->getConfig()['version'],
			]);
		}
	}

	public function getAdapter(){
		if($this->adapter){
			return $this->adapter;
		}
		else{
			return new AwsS3Adapter($this->getClient(), $this->getConfig()['bucket']);
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
}