<?php
//php vendor/bin/codecept run
namespace myprojecttests;

use AcceptanceTester;
use \Codeception\Util\Locator;

class InitialCest
{
    // Public methods
    // =========================================================================

    // Tests
    // =========================================================================

    public function _before(AcceptanceTester $I)
    {
        $I->signUpLogin($I);
    }

    public function testUserWelcomeTextInFooter(AcceptanceTester $I) {
        $I->amOnPage('/');
        $I->click('#data-menu-open');
        $I->see('Welcome back test');
    }

    public function changeAddress(AcceptanceTester $I)
    {
        $I->amOnPage('/members/update-account-details');
        $I->wait(1);
        $I->scrollTo('.purchaces__heading + ul > li + li .card-accordion__title', 0,-100);
        $I->click('.purchaces__heading + ul > li + li .card-accordion__title');
        $I->wait(2);
        $I->fillField('[name="address[firstName]"]', 'Mark');
        $I->fillField('[name="address[lastName]"]', 'Williams');
        $I->fillField('[name="address[businessName]"]', 'KOJO');
        $I->fillField('[name="address[address1]"]', '31 Fullerton Road');
        $I->fillField('[name="address[city]"]', 'Adelaide');
        $I->selectOption('[name="address[stateValue]"]', 'SA');
        $I->fillField('[name="address[zipCode]"]', '5000');
        $I->click('Add Address');
        $I->waitForText('Address Updated.');

        $I->selectOption('.address-select.select', 'Mark Williams - 31 Fullerton Road');
       // $I->canSeeInField('[name="address[firstName]"]', 'Mark');
    }


    public function updateAccountDetails(AcceptanceTester $I)
    {
        $I->amOnPage('/members/update-account-details');
        $I->wait(1);
        $I->scrollTo('.purchaces__heading + ul .card-accordion__title', 0, -100);
        $I->click('.purchaces__heading + ul .card-accordion__title');
        $I->wait(2);
        $I->fillField('[name="firstName"]', 'Test');
        $I->fillField('[name="lastName"]', 'User');
        $I->selectOption('[name="fields[state]"]', 'QLD');
        $I->fillField('[name="fields[dateOfBirth]"]', '01/01/2000');
        $I->fillField('[name="fields[phoneNumber]"]', '0434109641');

        $I->click('Update');
        $I->waitForText('Your contact details have been updated');
        $I->see('Your contact details have been updated');
       // $I->canSeeInField('[name="address[firstName]"]', 'Mark');
    }

    public function addCreditCard(AcceptanceTester $I)
    {
        $I->amOnPage('/members/update-account-details');
        $I->see('Update Account Details');
        $I->scrollTo('.purchaces__heading + ul > li + li +li .card-accordion__title', 0, -100);
        $I->click('.purchaces__heading + ul > li + li +li .card-accordion__title');
        $I->wait(3);

        //$I->selectOption('[name="paymentsource"]', 'Add New Card');

        $I->fillField('[name="fullName"]', 'John Smith');
        $I->fillField('[name="number"]', '4111111111111111');
        $I->fillField('[name="short_expiry"]', date('m/y', strtotime('+1 years')));
        $I->fillField('[name="cvv"]', '757');
        $I->fillField('//*[@id="gateway-2"]/div/div[4]/div/input', 'test');

        $I->scrollTo('[name="cvv"]');
        $I->click('.js-update-payment-source .boxed-button');

        $I->wait(3);

       // $I->selectOption('[name="paymentsource"]', 'John Smith');

       // $I->see('John Smith');
       // $I->canSeeInField('[name="address[firstName]"]', 'Mark');
    }


    public function removeCreditCard(AcceptanceTester $I)
    {


        $I->amOnPage('/members/update-account-details');
        $I->scrollTo('.purchaces__heading + ul > li + li + li .card-accordion__title', 0, -150);
        $I->click('.purchaces__heading + ul > li + li + li .card-accordion__title');
        $I->selectOption('#paymentSourceId', 'test');

        $I->wait(1);
        //$I->scrollTo('//*[@id="main"]/section/div/div/div/div/ul/li[3]/div/div[1]');

        $I->click('[value="Remove"]');
        $I->wait(1);

      //  $I->click('[name="paymentsource"]');

      //  $I->dontSee('Test'); 
       // $I->canSeeInField('[name="address[firstName]"]', 'Mark');
    }


    public function changePassword(AcceptanceTester $I)
    {
        $password = 'testuser';

        $I->amOnPage('/members/update-account-details');
        $I->see('Update Account Details');
        $I->scrollTo('.purchaces__heading');
        $I->click('//*[@id="main"]/section/div/div/div/div/ul/li[4]/div/div[1]');
        $I->wait(1);

        $I->fillField('.js-checkout-account-change-password [name="password"]', $password);
        $I->fillField('.js-checkout-account-change-password [name="newPassword"]', $password);
        $I->fillField('.js-checkout-account-change-password [name="confirmPassword"]', $password);

        $I->wait(1);

        $I->click('Change Password');

        //$I->waitForText('Your password has been changed'); 
        //$I->see('Your password has been changed'); 
       // $I->canSeeInField('[name="address[firstName]"]', 'Mark');
    }
/*
    public function testQuicklinks(AcceptanceTester $I)
    {
        $I->amOnPage('/');

        $I->scrollTo('.quicklinks', 0, -50); // this must be xpath link
        $I->see('Visit the wine room');
        $I->see('Private tours');
        $I->see('Who we are');
    }

*/

    public function showsSearchSuggestions(AcceptanceTester $I)
    {
        // if snapshot exists - skipping login
        //if ($I->loadSessionSnapshot('login')) return;

        $I->amOnPage('/');
        $I->click('[href="#search"]');
        $I->fillField('#search-input', 'caley');
    }
/*
    public function priceHighestFirst(AcceptanceTester $I)
    {
        // if snapshot exists - skipping login
        //if ($I->loadSessionSnapshot('login')) return;

        $I->amOnPage('/shop/wines');
        $I->wait(5);
        $I->see('365', '//*[@id="vue-container"]/section[7]/section/div/div/div/ul/li[1]/div[1]/div/div[2]/div[2]/div');
        $I->see('13.50', '//*[@id="vue-container"]/section[7]/section/div/div/div/ul/li[52]/div/div/div[2]/div[2]');
        $I->click('//*[@id="vue-container"]/div[9]/div/div/div[1]/form/div[2]/div/div/div[1]/div/div');

        $I->click('//*[@id="choices--sort-en-item-choice-2"]');
        $I->see('13.50', '//*[@id="vue-container"]/section[7]/section/div/div/div/ul/li[1]/div[1]/div/div[2]/div[2]/div');
        $I->see('365', '//*[@id="vue-container"]/section[7]/section/div/div/div/ul/li[52]/div/div/div[2]/div[2]');
    }


    public function priceLowestFirst(AcceptanceTester $I)
    {
        // if snapshot exists - skipping login
        //if ($I->loadSessionSnapshot('login')) return;

        $I->amOnPage('/shop/wines/?sort=defaultPrice|DESC');
        $I->wait(2);
        $I->see('13.50', '//*[@id="vue-container"]/section[7]/section/div/div/div/ul/li[1]/div[1]/div/div[2]/div[2]/div');
        $I->see('365', '//*[@id="vue-container"]/section[7]/section/div/div/div/ul/li[52]/div/div/div[2]/div[2]');
    }
*/

    public function addMembersItemToCart(AcceptanceTester $I)
    {
        $I->amOnPage('/shop/gifts/the-long-lunch-pack');

        $I->wait(5);
        $I->scrollTo('.product__content.js-update-product-content');
        $I->click('.featured-section .product-form__list-group--add-to-cart [value="Add to cart"]');
        $I->waitForText('Cart updated'); 
       // $I->see('cart-item__heading', 'The Long Lunch Pack');
    }

    public function checkout(AcceptanceTester $I)
    {
        $I->amOnPage('/shop/checkout');

        $I->wait(4); // wait for ajax to do it's thing
        $I->click('.js-update-cart-email-address [value="Next Step"]');

        $I->wait(4);
        $I->scrollTo('.js-update-cart-user-details [value="Confirm Details"]', 0, -150);
        $I->click('.js-update-cart-user-details [value="Confirm Details"]');

        $I->wait(6);

        try {
            $I->fillField('shippingAddress[businessName]', 'KOJO');
            $I->fillField('shippingAddress[address1]', '31 Fullerton Road');
            $I->fillField('shippingAddress[city]', 'Adelaide');
            $I->selectOption('shippingAddress[stateValue]', 'SA');
            $I->fillField('shippingAddress[zipCode]', '5000');
            $I->scrollTo('.js-checkout-address[data-address-type="billing"]', -100);
        } catch (\Exception $e) {

        }

        // $I->click('.js-update-cart-billing-shipping .form__checkout-label');
        
        $I->scrollTo('.js-update-cart-billing-shipping .checkout__continue-link', -150);
        $I->click('.js-update-cart-billing-shipping .checkout__continue-link');

        $I->wait(4);
        $I->fillField('#checkout-form-fullName', 'Test User');
        $I->fillField('#checkout-form-number', '4111111111111111');
        $I->fillField('#checkout-form-expiry', '02/22');
        $I->fillField('#checkout-form-cvv', '123');

        //$I->click('[name="savePaymentSource"]');

        $I->scrollTo('[value="Place order"]', -100);
        $I->click('[value="Place order"]');
        $I->wait(10);
        $I->see('Thank you.');
       // Next Step
    }

    public function removeAccount(AcceptanceTester $I) {
        $cancelAccountSelector = '//*[@id="main"]/section/div/div/div/div/ul/li[5]/div/div[1]';
        //  $I->wait($I);
        $I->amOnPage('/members/update-account-details');
        $I->scrollTo($cancelAccountSelector, 0, -150);
        $I->click($cancelAccountSelector);
        $I->wait(2);
        $I->click('Cancel my membership');
        $I->seeInPopup('This will permanently delete your account. Please confirm.');
        $I->acceptPopup(); //'OK'
        $I->wait(5);
    }

}
