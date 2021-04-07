<?php


namespace modules\Importer\Importers;


use modules\Importer\Components\S3SpreadSheetImporter;
use modules\Importer\Contracts\Importer;
use modules\Orders\Models\ProductModel;
use modules\Orders\Models\Records\ProductRecord;

/**
 * Imports Products into the yalumba_products table for use in phonetic dropdown field.
 * This data could also be used to look up product details from a phonetic.
 *
 * Class ProductsImporter
 * @package modules\Importer\Importers
 */
class ProductsImporter extends S3SpreadSheetImporter implements Importer {

	protected static $importerIdentifier = 'products';

	protected $path = 'product.csv';
	protected $record = ProductRecord::class;
	protected $skipFirst = true;
	protected $fields = [
	    0=> 'jdeProductNumber', //JDE_ITEM_NUMBER
		1=> 'phonetic',         //PHONETIC
		2=> 'primaryUnit',      //PRIMARY_UNIT
		3=> 'primaryUOMAllowSale',      //PRIMARY_UOM_ALLOW_SALE
        4=> 'secondaryUnit',            //SECONDARY_UNIT
		5=> 'secondaryUOMAllowSale',    //SECONDARY_UOM_ALLOW_SALE
		6=> 'primaryUnitPackSize',      //PRIMARY_UNIT_PACK_SIZE
		7=> 'primaryUnitsPerSecondary', //PRIMARY_UNITS_PER_SECONDARY
		8=> 'salesRangeKey',            //SALES_RANGE_KEY
		9=> 'salesRangeName',           //SALES_RANGE_NAME
		10=> 'brandKey',                //BRAND_KEY
		11=> 'brandName',               //BRAND_NAME
		12=> 'name',                    //NAME
		13=> 'nameShort',               //NAME_SHORT
		14=> 'pH',                      //PH
		15=> 'totalAcid',               //TOTAL_ACID
		16=> 'alcoholByVolume',         //ALCOHOL_BY_VOLUME
		17=> 'region',                  //REGION
		18=> 'productType',             //PRODUCT_TYPE
		19=> 'varietal',                //VARIETAL
		20=> 'vintage',                 //VINTAGE
		21=> 'closure',                 //CLOSURE
		22=> 'harvestDates',            //HARVEST_DATES
		23=> 'mediumImageUrl',          //LARGE_IMAGE_URL
		24=> 'smallImageUrl',           //MEDIUM_IMAGE_URL
		25=> 'brandImageUrl',           //BRAND_IMAGE_URL
        26=> 'tastingNoteUrl'           //TASTING_NOTE_URL
	];

	public function getIdentifier($row){
		return $row[0];
	}
}