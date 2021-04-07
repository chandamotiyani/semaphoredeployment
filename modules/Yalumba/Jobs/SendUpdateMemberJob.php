<?php


namespace modules\Yalumba\Jobs;


use craft\queue\JobInterface;
use craft\queue\QueueInterface;
use modules\Rezdy\RezdyModule;
use modules\Vend\VendModule;
use modules\Yalumba\YalumbaApiModule;
use yii\base\BaseObject;
use yii\queue\Queue;

/**
 * Class SendMemberJob
 * @package modules\Memberships\Jobs
 *
 * @property null|string $description
 */
class SendUpdateMemberJob extends BaseObject implements JobInterface {

	public $userId;
    public $userUid;
	public $groupIds;

	/**
	 * @return string|null
	 */
	public function getDescription() {
		return "Send Update Member to Yalumba API";
	}

	/**
	 * @param QueueInterface|Queue $queue
	 */
	public function execute( $queue ) {
		$user = \Craft::$app->users->getUserById($this->userId);
		if($user){
            YalumbaApiModule::getInstance()->api->sendYalumbaCustomer($user, $this->groupIds);
        }
	}

}