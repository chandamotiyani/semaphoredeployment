<?php


namespace modules\Importer\Importers;


use craft\commerce\models\Address;
use craft\commerce\models\PaymentSource;
use craft\web\User;
use modules\Importer\Components\LocalFileHandler;
use modules\Importer\Components\S3FileHandler;
use modules\Importer\Contracts\Importer;
use modules\Importer\Traits\HasImportIdentifier;
use modules\Importer\Traits\HasLog;
use craft\commerce\Plugin as Commerce;

/**
 *
 * !!DISABLE MEMBER EVENTS BEFORE RUNNING THIS!!
 * Initial Members import from data provided by Yalumba. Data includes payment tokens etc.
 * REMEMBER TO DELETE THE FILE WHEN YOU ARE DONE.
 *
 * Class InitialMembersImporter
 * @package modules\Importer\Importers
 */
class InitialMembersImporter implements Importer {
    use HasLog, HasImportIdentifier, ReadsSheet;
    protected static $importerIdentifier = "initial-members";
    protected $path = 'all-membersSAMPLE.csv';
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
                    try{
                        $this->inserter( $row);
                    }
                    catch (\Exception $e){
                        echo('<pre>');
                        var_dump($e->getMessage());
                        var_dump($e->getTraceAsString());
                        exit();
                    }
                }
                $count++;
            }
        });
        echo("DONE.");
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
        //does this user exist?
        if($this->getRowData($row, 'email')){
            $user = \craft\elements\User::find()->email($this->getRowData($row, 'email'))->one();
            if(!$user){
                $user = new \craft\elements\User([
                         'username' => $this->getRowData($row, 'email'),
                         'newPassword' => md5(uniqid()), //we assign random passwords- people are being sent reset keys.
                         'email' => $this->getRowData($row, 'email'),
                         'admin' => false
                     ]);
            }
            $user->firstName = $this->getRowData($row, 'first_name');
            $user->lastName = $this->getRowData($row, 'last_name');
            $user->phoneNumber = $this->getRowData($row, 'phone');
            $dob =  $this->getRowData($row, 'year_of_birth')."-".
                    $this->getRowData($row, 'month_of_birth')."-".
                    $this->getRowData($row, 'day_of_birth');

            $dob = \DateTime::createFromFormat("Y-m-d", $dob);
            $user->dateOfBirth = $dob;
            $user->yalumbaCustomerId = $this->getRowData($row, 'site_user_id');

            //SAVE THE USER
            \Craft::$app->getElements()->saveElement($user);
            $customer = Commerce::getInstance()->customers->getCustomerByUserId($user->id);

            //BILLING ADDRESS
            $address = new Address();
            if($this->getRowData($row, 'billing_address')){
                $address->firstName = $this->getRowData($row, 'first_name');
                $address->lastName = $this->getRowData($row, 'last_name');
                $address->address1 = $this->getRowData($row, 'billing_address');
                $address->city = $this->getRowData($row, 'billing_city');
                $address->zipCode = $this->getRowData($row, 'billing_postcode');
                $address->countryId = 13;
                if($this->getRowData($row, 'billing_state')){
                    $address->stateId = $this->lookupState($this->getRowData($row, 'billing_state'))->id;
                }
                $address->businessName = $this->getRowData($row, 'billing_company');
                Commerce::getInstance()->customers->saveAddress($address, $customer);
            }

            //SHIPPING ADDRESS
            $shippingAddress = new Address();
            if($this->getRowData($row, 'shipping_address')){
                $shippingAddress->firstName = $this->getRowData($row, 'first_name');
                $shippingAddress->lastName = $this->getRowData($row, 'last_name');
                $shippingAddress->address1 = $this->getRowData($row, 'shipping_address');
                $shippingAddress->city = $this->getRowData($row, 'shipping_city');
                $shippingAddress->zipCode = $this->getRowData($row, 'shipping_postcode');
                $shippingAddress->countryId = 13;
                if($this->getRowData($row, 'shipping_state')){
                    $shippingAddress->stateId = $this->lookupState($this->getRowData($row, 'shipping_state'))->id;
                }
                $shippingAddress->businessName = $this->getRowData($row, 'shipping_company');
                Commerce::getInstance()->customers->saveAddress($shippingAddress, $customer);
            }

            //PAYMENT SOURCE
            if($this->getRowData($row, 'card_status') == 'active') {
                $paymentSource = new \modules\Spreedly\Models\PaymentSource(
                    [
                        'gatewayId'   => '2', //hard coded - I don't want to have to look this up.
                        'token'       => $this->getRowData($row, 'card_token'),
                        'description' => $this->getRowData($row, 'card_number'),
                        'response'    => ['imported_from_yalumba.com' => true],
                        'userId'      => $user->id
                    ]
                );
                Commerce::getInstance()->paymentSources->savePaymentSource($paymentSource );
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
            $rows = [];
            foreach($keys as $key){
                $rows[] = $row[$key];
            }
            return $rows;
        }
        if(isset($row[$keys])){
            return $row[$keys];
        }
        return '';
    }

    public function lookupState($state){
        $states = Commerce::getInstance()->states->getAllStates();
        foreach($states as $thisState){
            if($thisState->abbreviation == $state){
                return $thisState;
            }
        }
    }
}