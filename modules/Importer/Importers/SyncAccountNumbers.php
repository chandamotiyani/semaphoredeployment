<?php


namespace modules\Importer\Importers;

use Craft;
use craft\helpers\DateTimeHelper;
use craft\web\User;
use modules\Importer\Components\S3FileHandler;
use modules\Importer\Components\S3SpreadSheetImporter;
use modules\Importer\Contracts\Importer;
use craft\commerce\Plugin as Commerce;
use modules\Importer\Traits\HasImportIdentifier;
use modules\Importer\Traits\HasLog;
use modules\Orders\Models\OrderModel;
use modules\Orders\Models\ProductModel;
use modules\Orders\Models\Records\OrderRecord;
use modules\Orders\Models\Records\ProductRecord;
use modules\Orders\OrdersModule;

/**
 * Import orders from yalumba batch file. Order contents comes from LineItemsImporter. You
 * should run LineItemsImporter after running this.
 *
 * Class OrdersImporter
 * @package modules\Importer\Importers
 */
class SyncAccountNumbers extends S3SpreadSheetImporter implements Importer
{

    use ReadsSheet, HasLog, HasImportIdentifier;

    protected static $importerIdentifier = 'sync-account-numbers';

    protected $path = 'full-production-export.csv';
    protected $record = OrderRecord::class;
    protected $skipFirst = true;
    protected $fields = [
        0 => 'orderNumber',
        1 => 'accountNumber',
        2 => 'accountName',
        3 => 'orderDate',
        4 => 'orderId', //customer order number
        6 => 'status'
    ];


    /**
     * most files have a row or 2 of additional stuff at the top (ie column headers etc)
     * we don't want to import these - this defines the start row of data.
     * @var
     */
    protected $startRow = 2;

    public function getRecord()
    {
        return $this->record;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function getFields()
    {
        return $this->fields;
    }

    public function getStartRow()
    {
        return $this->startRow;
    }
    
    public function import($options = []) {
        //initially the file is on s3.
        $file = new S3FileHandler(\Craft::$app->getConfig()->getConfigFromFile('services')['yalumba-data-feed']);
        // We read the sheet and provide a function that will run per chunk received
        // from the spreadsheet reader. The chunked approach saves memory as only 20
        // spreadsheet rows are loaded at 1 time. This is a bit moot if we are just
        // reading a csv.
        $count = 1;

        $this->log("Downloaded File for processing to $this->fullLocalPath", get_class($this));

        $this->readSheet($file)->processRows($this->getStartRow(), function ($sheet) use ($count) {
            $this->log("Processing chunked rows", get_class($this));
            // we get the rows returned by the chunked sheet
            $rows = $sheet->getActiveSheet()->rangeToArray($sheet->getActiveSheet()->calculateWorksheetDataDimension());
            try {
                if ($rows) {
                    foreach ($rows as $key => $row) {
                        //$this->log("Processing rows", get_class($this));
                        $recordClassName = $this->getRecord();
                        $record = new $recordClassName();
                        // by saving an identifier with the row we can keep track of duplicates etc.
                        $record->identifier = trim($this->getIdentifier($row), '"');


                        $existing = $this->getRecord()::find()->where(['identifier' => $record->identifier])->one();

                        if ($existing) {
                            $record = $existing;
                        }

                        // foreach field defined in the import object, we attempt to save it out (per the mapping)
                        // to the active record object (also defined in the import object)
                        foreach ($this->getFields() as $key => $field) {
                            if (isset($row[$key])) {
                                if (method_exists($this, 'get_' . $field)) {
                                    // this is where the magic method happens - we call get_myField from the import
                                    // object if it exists.
                                    $record->$field = $this->{'get_' . $field}($row);
                                } else {
                                    // if there's no magic method, save using the row key.
                                    $record->$field = $row[$key];
                                }
                            }
                        }
                        if ($record->identifier) {
                            $record->accountNumber = trim($this->getAccountNumber($row), '"');

                            // make sure we've got an identifier, if so, we can save the record into the DB.
                            //$record = $this->before_row_save($record);
                            //$record->save();
                            $this->after_row_save($record);
                            //$this->log("Saved row", get_class($this));
                        }
                        $count++;
                        unset($record);
                    }
                }
            } catch (\Exception $ex) {
                $this->log($ex->getMessage(), get_class($this));
            }
            unset($sheet);
            unset($rows);
        });
        $this->log("Completed processing $count rows", 'events');
        // Chanda - commenting following line, so that files downloaded not get deleted
        //unset($file);

    }

    public function get_orderDate($row)
    {
        return DateTimeHelper::toDateTime(\DateTime::createFromFormat('d/m/Y', $row[3]));
    }



    public function getIdentifier($row)
    {
        return $row[0];
    }

    public function getAccountNumber($row) {
        return $row[1];
    }

    public function after_row_save($orderModel)
    {
        $orderModel->orderNumber = trim($orderModel->orderNumber, '"');
        $yalumbaOrder = OrderRecord::find()->where(['orderNumber' => $orderModel->orderNumber])->one();


        if ($yalumbaOrder) {
            $user = \craft\elements\User::find()->yalumbaCustomerId($orderModel->accountNumber)->one();
            if ($user) {
                try {
                    $customer = Commerce::getInstance()->getCustomers()->getCustomerByUserId($user->id);
                    $yalumbaOrder->accountNumber =  $customer->id;
                    if($result = $yalumbaOrder->save()) {

                        $this->log("Account number updated with:".$customer->id." for Order:".$orderModel->orderNumber);
                    }
                }  catch (\Exception $ex) {
                    $this->log($ex->getMessage(), 'Order ' . $orderModel->orderNumber);
                }
            } else {
                $this->log("Yalumba Customer id:".$orderModel->accountNumber." not found hence, cannot update account number for Order:".$orderModel->orderNumber);
            }
        } else {
            $this->log("Order:".$orderModel->orderNumber." not found.");
        }
    }
}
