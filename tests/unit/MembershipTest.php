<?php

namespace Tests\unit;

use Codeception\Test\Unit;

use craft\elements\User;
use craft\services\Users;
use modules\Events\EventsModule;
use modules\Yalumba\Jobs\SendCreateMemberJob;
use modules\Memberships\MembershipModule;
use UnitTester;
use \Craft;

class MembershipTest extends Unit
{
	/**
	 * @var UnitTester
	 */
	protected $tester;

	protected $module;


	public function _before(){
		$this->module = MembershipModule::getInstance();
	}

	public function testCreateUser(){

	}

	public function createUser(){
		$user = new User();
		$user->email = 'dfsgfsd@dsfggfd.com';
		$user->username = 'gdtgdfsgds';
		$user->firstName = 'gdtgdfsgds';
		$user->lastName = 'gdtgdfsgds';
		Craft::$app->elements->saveElement($user);
		return $user;
	}

	public function testMemberCreate(){
		$user =  $this->createUser();
		$group = Craft::$app->userGroups->getGroupByHandle('insider');
		$this->assertEquals($group->handle, 'insider');
		$groupIds = [$group->id];
		$this->assertNotEmpty($groupIds);

		$this->tester->expectEvent(Users::class,Users::EVENT_AFTER_ASSIGN_USER_TO_GROUPS,function() use($user, $groupIds){
			//test the event was fired when user is assigned to a group..
			Craft::$app->users->assignUserToGroups($user->id,$groupIds);
		});

		//the user should have been sent to vend and yalumba api and now have their respective ids from their apis.
        //THIS TEST WILL FAIL AT THE MOMENT SINCE THESE ARE INSERTED VIA A QUEUED JOB
		$this->assertNotEmpty($user->vendCustomerId);
		$this->assertNotEmpty($user->yalumbaCustomerId);

	}



	public function memberUserGroupProvider(){
        return [
            [
                [
                    "4",
                    "2",
                ],
            ],
            [
                [
                    "2",
                    "4",
                ],
            ],
            [
                [
                    "2",
                    "3",
                ],
            ],
            [
                [
                    "3",
                    "2",
                ],
            ],
        ];
    }

    /**
     * @param $groupIds
     * @dataProvider memberUserGroupProvider
     */
    public function testGetYalumbaMemberUserGroup($groupIds){
        $result = $this->module->members->getYalumbaMemberUserGroup($groupIds);
        $this->assertEquals("adventurer", $result->handle);
    }

}
