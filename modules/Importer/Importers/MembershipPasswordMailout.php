<?php


namespace modules\Importer\Importers;


use craft\elements\User;
use modules\Importer\Components\LocalFileHandler;
use modules\Importer\Components\S3FileHandler;
use modules\Importer\Contracts\Importer;
use modules\Importer\Traits\HasImportIdentifier;
use modules\Importer\Traits\HasLog;
use modules\Memberships\MembershipModule;

/**
 * EXPORT (note not import) the membership password reset links for mailout by Yalumba systems
 *
 * Class MembershipPasswordMailout
 * @package modules\Importer\Importers
 */
class MembershipPasswordMailout implements Importer
{
    use HasLog, HasImportIdentifier;
    protected static $importerIdentifier = 'membership-password-mailout';
    private $path = 'export.csv';

    /**
     * DOES NOT IMPORT ANYTHING - this function actually _exports_ the current members
     * and the password reset link. This is for the initial memberships from the old
     * site. The resulting file should be deleted after use - it contains sensitive
     * information.
     */
    public function import()
    {

        $localFileHandler = new LocalFileHandler( \Craft::$app->getConfig()->getConfigFromFile( 'services' )['local'] );
        $file = fopen($localFileHandler->getFilePath($this->path), 'w');
        foreach(MembershipModule::getInstance()->members->getMembershipGroups() as $membershipGroup){
            $users = User::find()->group($membershipGroup)->all();
            foreach($users as $user){
                $row['email']= $user->email;
                $row['reset']= \Craft::$app->users->getPasswordResetUrl($user);

                fputcsv($file,$row);
            }
        }
        fclose($file);
    }

}