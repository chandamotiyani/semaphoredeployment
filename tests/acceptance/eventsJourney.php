<?php
/**
 * @author Chanda
 * @description JOURNEY 2 - Jesse's Events Journey
 */


namespace myprojecttests;

use AcceptanceTester;
use \Codeception\Util\Locator;


class eventsJourney
{
    public function _before(AcceptanceTester $I)
    {
        $I->signUpLogin($I);
    }

    public function addTour(AcceptanceTester $I)
    {
        $I->amOnPage('/winery-tours');
        $I->wait(4); // wait for ajax to do it's thing
    }
}