<?php


namespace modules\Importer\Components;


use modules\Importer\Contracts\Importer;
use modules\Importer\Importers\ReadsSheet;
use modules\Importer\Traits\HasImportIdentifier;
use modules\Importer\Traits\HasLog;

class S3SpreadSheetImporter implements Importer
{
    use ReadsSheet, HasLog, HasImportIdentifier;

    /**
     * The class name for the active record object that will get saved.
     * @var
     */
    protected $record;

    /**
     * The path to the file that should be saved
     * @var
     */
    protected $path;

    /**
     * The fields that should be saved
     * @var
     */
    protected $fields;

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

    /**
     * You can also attach magic methods to this class that will get saved to the database when it
     * runs into that field -
     * public function get_myField($row){
     *      return $row[0].$row[1] // would save myField into the record with the provided value.
     * }
     */

    /**
     * Runs the import. Your importer should either use this method (ie extend it) or implement
     * it's own version of this.
     *
     * @param array $options
     */
    public function import($options = [])
    {
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
                        $record->identifier = $this->getIdentifier($row);
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
                            // make sure we've got an identifier, if so, we can save the record into the DB.
                            $record = $this->before_row_save($record);
                            $record->save();
                            $this->after_row_save($record);
                            $this->log("Saved row", get_class($this));
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

    public function after_row_save($record)
    {

    }

    public function before_row_save($record)
    {
        return $record;
    }
}