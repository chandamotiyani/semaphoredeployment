<?php

namespace myprojecttests;

use FunctionalTester;

class OrderingCest
{
    // Public methods
    // =========================================================================

    // Tests
    // =========================================================================

    /**
     * @param FunctionalTester $I
     */
    public function home(FunctionalTester $I)
    {
        $I->amOnPage('/');
        $I->amLoggedInAs(9);
        $I->see('© 2020 Yalumba');
        $I->canSeeInTitle('Yalumba');
    }

    public function login(FunctionalTester $I){
        $I->amOnPage('/members/sign-in');
        //$I->see('Having trouble signing in or forgotten your password?');
        $I->fillField(".login-address input", "simon.davies@kojo.com.au");
        $I->fillField(".login-signin-pw input", "password");
        $I->click(".login-signin-submit");
        $I->wait(10);
        $I->amOnPage('/members/my-membership');
        $I->makeScreenshot('logged_in_page');
    }
}
