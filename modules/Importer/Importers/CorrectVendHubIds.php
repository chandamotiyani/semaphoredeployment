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
class CorrectVendHubIds implements Importer {
    use HasLog, HasImportIdentifier, ReadsSheet;
    protected static $importerIdentifier = "correct-vend-hub-ids";
    private $fullLocalPath;
    private $startRow=0;
    private $titles = [];

    public function import() {
        $users = \craft\elements\User::find()->all();
        if($users) {

            $this->log("Re-saving following users so that they get correct Hub Ids in Vend, Please Note: it will re save all the users whose yalumbaHubId is not empty!!");
            foreach($users as $user){

                if(!empty($user->yalumbaHubId)){
                    // re-saving users will trigger the vend API get the correct Hub Ids for all the users in VEND
                    $saved = \Craft::$app->getElements()->saveElement($user, false);
                    $this->log("$user->email", 'User: ');
                }
            }
        }
    }

}