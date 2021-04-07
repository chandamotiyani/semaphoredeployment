<?php


namespace modules\Importer\Importers;


use modules\Importer\Contracts\Importer;
use modules\Importer\ImporterModule;
use modules\Importer\Traits\HasImportIdentifier;
use modules\Importer\Traits\HasLog;
use modules\Rezdy\Models\Records\APIEventRecord;
use modules\Rezdy\RezdyModule;
/**
 * Imports events from Rezdy into the yalumba_events table for use in events picker and
 * and Rezdy booking process.
 *
 * Class EventScheduleImporter
 * @package modules\Importer\Importers
 */
class EventsImporter implements Importer {

	use HasLog, HasImportIdentifier;

	protected static $importerIdentifier = 'events';

	public function import() {
		//get rezdy products
		$this->log("Getting data from Rezdy");
		$response = RezdyModule::getInstance()->api->getRezdyProducts();
		$this->log("Received data from Rezdy");
		foreach($response->getData() as $product){
			$this->log("Processing Rezdy Product $product->productCode");
			$record = new APIEventRecord();
			$record->identifier = $product->productCode;
			//update existing items.
			$existing = APIEventRecord::find()->where(['identifier'=>$record->identifier])->one();
			if($existing){
				$record = $existing;
			}

			$record->name = $product->name;
			$record->shortDescription = $product->shortDescription;
			$record->description = $product->description;

			if($product->minimumNoticeMinutes){
                $record->minimumNotice = (int)$product->minimumNoticeMinutes/60;
            }
			//TODO: probably price etc too..
			//maybe an image, booking type etc.

			$record->save();
			$this->log("Saving Rezdy Product $record->id : $product->productCode");
		}
	}
}