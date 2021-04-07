<?php


/**
 * Inherited Methods
 * @method void wantToTest($text)
 * @method void wantTo($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method void pause()
 *
 * @SuppressWarnings(PHPMD)
*/
class AcceptanceTester extends \Codeception\Actor
{
    use _generated\AcceptanceTesterActions;

   /**
    * Define custom actions here
    */

    public $cookie;

    public function signUpLogin(AcceptanceTester $I)
    {

        if ($I->loadSessionSnapshot('login')) {
            return;
        }

        $I->amOnPage('/members/sign-up');
        $I->click('.cookies-notice__buttons > a');
        $I->wait(3);
        $I->fillField('[name="firstName"]', 'Test');
        $I->fillField('[name="lastName"]', 'User');
        $I->fillField('[name="email"]', 'test.user@kojo.com.au');
        $I->selectOption('[name="fields[state]"]', 'SA');
        $I->fillField('[name="fields[dateOfBirth]"]', '01/01/2001');
        $I->fillField('.js-checkout-account-create [name="password"]', 'testuser');
        $I->click('Create Account');
        $I->wait(3);
        //$I->see('Your contact details have been updated');
        try {
            $I->see('has already been taken');

            // login
            $I->login($I);
        } catch (\Exception $e) {
            $I->see('Thank you for being one of our');
        }

        $I->saveSessionSnapshot('login');

    }

    public function login(AcceptanceTester $I) {
        try {
            $I->click('.cookies-notice__buttons > a');
        } catch (\Exception $e) {

        }

        $I->amOnPage('/members/sign-in');

        $I->fillField('.section__body .login-address input', 'test.user@kojo.com.au');
        $I->fillField('.section__body .login-signin-pw input', 'testuser');
        //$I->scrollTo('.login-signin-submit');
        //$I->wait(3);
        $I->click('.checkout__form-row--center > .login-signin-submit');
       // $I->click('#data-menu-open');
        $I->waitForText("Thank you");
        $I->see("Thank you");
    }
    
}