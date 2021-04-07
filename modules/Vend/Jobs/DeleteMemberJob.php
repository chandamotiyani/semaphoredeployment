<?php


namespace modules\Vend\Jobs;


use craft\elements\User;
use craft\queue\JobInterface;
use craft\queue\QueueInterface;
use modules\Rezdy\RezdyModule;
use modules\Vend\VendModule;
use modules\Yalumba\YalumbaApiModule;
use yii\base\BaseObject;

/**
 * Class DeleteMemberJob
 * @package modules\Memberships\Jobs
 *
 * @property string $description
 */
class DeleteMemberJob extends BaseObject implements JobInterface {

	public $userId;
    public $userUid;

	public function getDescription() {
		return "Deletes Members from Vend";
	}

	public function execute( $queue ) {
        //note we find the element like this incase it's already been trashed.
		$user = User::find()->trashed()->id($this->userId)->one();
		VendModule::getInstance()->api->deleteMember($user);
	}

}