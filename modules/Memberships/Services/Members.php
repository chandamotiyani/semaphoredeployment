<?php


namespace modules\Memberships\Services;

use craft\base\Component;
use craft\commerce\Plugin as Commerce;
use craft\elements\Entry;
use craft\elements\User;
use craft\models\UserGroup;
use modules\Orders\Models\LineItemModel;
use modules\Orders\Models\Records\LineItemRecord;
use modules\Orders\Models\OrderModel;
use modules\Orders\Models\Records\OrderRecord as OrderRecord;
use \Craft;

class Members extends Component {


	/**
	 * Orders constructor.
	 */
	public function __construct() {

	}


    /**
     * @param $user
     *
     * @return array
     */
    public function getUserGroupIds($user){
        $groups = Craft::$app->userGroups->getGroupsByUserId($user->id);

        return array_map(function($group) {
            return $group->id;
        }, $groups);
    }

    /**
     * check the dns is valid on an email address - Rezdy checks email address dns, so to prevent errors,
     * we need to do the same.
     * @param $email
     *
     * @return bool
     */
    public function isValidEmail($email){
        return checkdnsrr(substr(strrchr($email, "@"), 1));
    }
    
    /**
     * gets array og Yalumba membership groups for use with memberships. Based on config, excludes
     * yalumba admin groups etc.
     */
    public function getMembershipGroups(){
        $memberGroupHandles = \Craft::$app->getConfig()->getConfigFromFile('membership')['yalumba-membership'];
        $groups = [];
        foreach($memberGroupHandles as $membverGroupHandle){
            $groups[] = \Craft::$app->userGroups->getGroupByHandle($membverGroupHandle);
        }
        return $groups;
    }


	/**
	 * Provide a list of userGroups (ids or models) and will return with the most relevant
     * usergroup model we should apply. For example, if a member is in the insider and
     * adventurer group, we'd want to give them permissions of the adventurer
	 *
	 * @param array $groups
	 *
	 * @return bool|mixed
	 */
	public function getYalumbaMemberUserGroup(array $groups){
		$count = count($groups);
		if($count == 1){
		    if(!is_object($groups[0])){
                return Craft::$app->userGroups->getGroupById($groups[0]);
            }
			else{
                // $groups[0] is a group object.
			    return $groups[0];
            }
		}
		if($count > 1){
			if(is_object($groups[0]) && get_class($groups[0]) == UserGroup::class){
				$userGroups = $groups;
			}
			else{
				//we've got an array of id's. Need to get the models.
                foreach($groups as $group){
                    $userGroups[] = \Craft::$app->userGroups->getGroupById($group);
                }
			}
		}
		// we now have a an array of MembershipGroup objects.
		if(isset($userGroups)){
            $membershipHandles = \Craft::$app->getConfig()->getConfigFromFile('membership')['yalumba-membership'];
            foreach($membershipHandles as $memberHandle){
                foreach($userGroups as $userGroup){
                    if($memberHandle == $userGroup->handle){
                        return $userGroup;
                    }
                }
            }
		}
		return false;
	}


}