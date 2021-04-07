<?php


namespace modules\Importer\Services;

use craft\base\Component;
use \Craft;
use modules\Importer\Errors\ImportException;
use modules\Importer\Importers\EventScheduleImporter;
use modules\Importer\Importers\EventsImporter;
use modules\Importer\Importers\InitialMemberGroupImporter;
use modules\Importer\Importers\InitialMemberJDENumberImporter;
use modules\Importer\Importers\LineItemsImporter;
use modules\Importer\Importers\InitialMembersImporter;
use modules\Importer\Importers\MembershipPasswordMailout;
use modules\Importer\Importers\OrdersImporter;
use modules\Importer\Importers\ProductsImporter;
use yii\db\Exception;
use modules\Importer\Importers\BulkUpdateHubId;
use modules\Importer\Importers\SyncAccountNumbers;
use modules\Importer\Importers\CorrectVendHubIds;

class Importer extends Component {

	private $logEnabled = true;

	// a list of importers that can run
	private $importers = [
		EventsImporter::class,
		EventScheduleImporter::class,
		ProductsImporter::class,
		OrdersImporter::class,
		LineItemsImporter::class,
        InitialMembersImporter::class,
        MembershipPasswordMailout::class,
        InitialMemberGroupImporter::class,
        InitialMemberJDENumberImporter::class,
        BulkUpdateHubId::class,
        SyncAccountNumbers::class,
        CorrectVendHubIds::class,
	];

	public function setImporters($importers){
		$all = $this->getKeyedImporters();
		if(is_array($importers)){
			foreach($importers as $importer){
				$out[] = $all[$importers];
			}
		}
		else{
			if(isset($all[$importers])){
				$out[] = $all[$importers];
			}
		}
		if(!isset($out)){
			throw new ImportException('No valid importer methods were provided');
		}
		$this->importers = $out;
		return $this->importers;
	}

	public function getKeyedImporters(){
		$out = [];
		foreach($this->importers as $importer){
			$out[$importer::getImporterIdentifier()] = $importer;
		}
		return $out;
	}

	public function setLog($value){
		$this->logEnabled = $value;
		return $this;
	}

	public function log($message, $category='importer'){
		if($this->logEnabled){
			Craft::getLogger()->log($message, \yii\log\Logger::LEVEL_TRACE, $category);
		}
	}

	public function import(){
		$itemsToImport = $this->getKeyedImporters();
		try{
			$this->log("Batch Importer started. ".count($itemsToImport) . " batches to import", 'importer');
			$count = '1';
			foreach($itemsToImport as $key=>$importerClassName){
				$this->log("Batch $count: $key Importer started.", $key);
				$importerInstance = new $importerClassName();
				$importerInstance->import();
				$importerInstance = NULL;
				unset($importerInstance);
				$this->log("Batch $count: $key Importer completed.", $key);
				$count++;
			}
		}
		catch(\Exception $e){
			$this->log("BATCH IMPORTER FAILED. ".count($itemsToImport) . " batches to import", 'importer');
		}

	}
}