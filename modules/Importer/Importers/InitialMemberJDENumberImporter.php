<?php


namespace modules\Importer\Importers;


use craft\commerce\models\PaymentSource;
use craft\web\User;
use modules\Importer\Components\LocalFileHandler;
use modules\Importer\Components\S3FileHandler;
use modules\Importer\Contracts\Importer;
use modules\Importer\Errors\ImportException;
use modules\Importer\Traits\HasImportIdentifier;
use modules\Importer\Traits\HasLog;
use craft\commerce\Plugin as Commerce;

/**
 * !!DISABLE MEMBER EVENTS BEFORE RUNNING THIS!!
 * Initial import of the member groups the members are a part of. Loop through all items in the file and
 * look up a craft use with the same yalumbaCustomerId. Update the vend api id and assign the member groups.
 *
 * Class InitialMemberGroupImporter
 * @package modules\Importer\Importers
 */
class InitialMemberJDENumberImporter implements Importer {
    use HasLog, HasImportIdentifier, ReadsSheet;
    protected static $importerIdentifier = "initial-member-jde";
    protected $path = 'memberJDE.xlsx';
    private $fullLocalPath;
    private $startRow=0;
    private $titles = [];

    public function import() {
        $localFileHandler = new LocalFileHandler( \Craft::$app->getConfig()->getConfigFromFile( 'services' )['local'] );
        $count = 1;
        $titles = [];
        $this->fullLocalPath = $localFileHandler->getFilePath( $this->getPath() );
        $this->processRows($this->getStartRow(), function($sheet) use (&$count) {
            // we get the rows returned by the chunked sheet
            $rows = $sheet->getActiveSheet()->rangeToArray($sheet->getActiveSheet()->calculateWorksheetDataDimension());
            foreach ($rows as $key => $row) {
                if($count == 1){
                    //this is the titles
                    $this->titles = $row;
                }
                else{
                    $allRows[] = $row;
                    $this->inserter( $row);
                }
                $count++;
            }
        });
        // TODO: Implement import() method.
        //This is the importer for the initial import of all members.
    }

    public function getPath(){
        return $this->path;
    }

    public function getStartRow(){
        return $this->startRow;
    }

    public function inserter($row){

        //we need to find the current craft user with this ID
        if($this->getRowData($row, 'user id')){
            $hub = $this->getRowData($row, 'user id');
            $jde = $this->getRowData($row, 'jde customer number');
            $users = \craft\elements\User::find()->yalumbaCustomerId($hub)->all();
            foreach($users as $user){
                if($user->email == $this->getRowData($row, 'email')){
                    $user->setFieldValue('yalumbaCustomerId', $jde);
                    $user->setFieldValue('yalumbaHubId', $hub);
                    $saved = \Craft::$app->getElements()->saveElement($user, false);
                }
            }
        }
    }

    public function getColumnIndex($field){
        $result = array_keys($this->titles, $field );
        if(count($result) === 1){
            return $result[0];
        }
        return $result;
    }

    public function getRowData($row, $field){
        $keys = $this->getColumnIndex($field);
        if(is_array($keys)){
            if(count($keys)){
                $rows = [];
                foreach($keys as $key){
                    $rows[] = $row[$key];
                }
                return $rows;
            }else{
                throw new ImportException("Could not find column ".$field);
            }
        }
        return $row[$keys];
    }

    private function getGroup($groupName){
        return \Craft::$app->userGroups->getGroupByHandle(trim(strtolower($groupName)));
    }
}