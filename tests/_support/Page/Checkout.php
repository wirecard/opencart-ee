<?php
/**
 * Shop System Plugins - Terms of Use
 *
 * The plugins offered are provided free of charge by Wirecard AG and are explicitly not part
 * of the Wirecard AG range of products and services.
 *
 * They have been tested and approved for full functionality in the standard configuration
 * (status on delivery) of the corresponding shop system. They are under General Public
 * License version 3 (GPLv3) and can be used, developed and passed on to third parties under
 * the same terms.
 *
 * However, Wirecard AG does not provide any guarantee or accept any liability for any errors
 * occurring when used in an enhanced, customized shop system configuration.
 *
 * Operation in an enhanced, customized configuration is at your own risk and requires a
 * comprehensive test phase by the user of the plugin.
 *
 * Customers use the plugins at their own risk. Wirecard AG does not guarantee their full
 * functionality neither does Wirecard AG assume liability for any disadvantages related to
 * the use of the plugins. Additionally, Wirecard AG does not guarantee the full functionality
 * for customized shop systems or installed plugins of other vendors of plugins within the same
 * shop system.
 *
 * Customers are responsible for testing the plugin's functionality before starting productive
 * operation.
 *
 * By installing the plugin into the shop system the customer agrees to these terms of use.
 * Please do not use the plugin if you do not agree to these terms of use!
 *
 * @author Wirecard AG
 * @copyright Wirecard AG
 * @license GPLv3
 */

namespace Page;

use \Codeception\Util\Locator;

class Checkout extends Base
{

    // include url of current page
    /**
     * @var string
     * @since 1.4.0
     */
    public $URL = 'checkout';

    /**
     * @var array
     * @since 1.4.0
     */

    public $elements = array(
        'Checkout Options' => '//*[@class="radio"]',
        'Continue1' => '//*[@value="Continue"]',
        'First Name' => '//*[@name="firstname"]',
        'Last Name' => '//*[@name="lastname"]',
        'Email' => '//*[@id="input-payment-email"]',
        'Telephone' => '//*[@name="telephone"]',
        'Address1' => '//*[@id="input-payment-address-1"]',
        'City' => '//*[@name="city"]',
        'Post Code' => '//*[@name="postcode"]',
        'Country' => '//*[@name="country_id"]',
        'Region/State' => '//*[@name="zone_id"]',
        'Continue2' => '//*[@id="button-guest"]',
        'Continue' => '//*[@id="button-payment-method"]',
        'Order Table' => '//*[@class="table-responsive"]',
        'Wirecard Credit Card' => '//*[@name="payment-option"]',
        'Credit Card First Name' => '//*[@id="first_name"]',
        'Credit Card Last Name' => '//*[@id="last_name"]',
        'Credit Card Card number' => '//*[@id="account_number"]',
        'Credit Card CVV' => '//*[@id="card_security_code"]',
        'Credit Card Valid until month' => '//*[@id="expiration_month_list"]',
        'Credit Card Valid until year' => '//*[@id="expiration_year_list"]',
        'Confirm Order' => '//*[@id="button-confirm"]',
        'I have read and agree to the Terms & Conditions' => '//*[@name="agree"]',
    );

    /**
     * Method fillBillingDetails
     *
     * @since 1.4.0
     */
    public function fillBillingDetails()
    {
        $I = $this->tester;
        $data_field_values = $I->getDataFromDataFile('tests/_data/CustomerData.json');

        $I->waitForElementVisible($this->getElement('First Name'));
        $I->fillField($this->getElement('First Name'), $data_field_values->first_name);
        $I->waitForElementVisible($this->getElement('Last Name'));
        $I->fillField($this->getElement('Last Name'), $data_field_values->last_name);
        $I->waitForElementVisible($this->getElement('Email'));
        $I->fillField($this->getElement('Email'), $data_field_values->email_address);
        $I->waitForElementVisible($this->getElement('Telephone'));
        $I->fillField($this->getElement('Telephone'), $data_field_values->phone);
        $I->waitForElementVisible($this->getElement('Address1'));
        $I->fillField($this->getElement('Address1'), $data_field_values->street_address);
        $I->waitForElementVisible($this->getElement('City'));
        $I->fillField($this->getElement('City'), $data_field_values->town);
        $I->waitForElementVisible($this->getElement('Post Code'));
        $I->fillField($this->getElement('Post Code'), $data_field_values->post_code);
        $I->waitForElementVisible($this->getElement('Country'));
        $I->selectOption($this->getElement('Country'), $data_field_values->country);
        $I->wait(1);
        $I->waitForElementVisible($this->getElement('Region/State'));
        $I->selectOption($this->getElement('Region/State'), $data_field_values->state);
        $I->waitForElementVisible($this->getElement('Continue2'));
        $I->click($this->getElement('Continue2'));
        $I->wait(1);
    }

    /**
     * Method fillCreditCardDetails
     * @since 1.4.0
     */
    public function fillCreditCardDetails()
    {
        $I = $this->tester;
        $data_field_values = $I->getDataFromDataFile('tests/_data/CardData.json');
        $I->waitForElementVisible($this->getElement('Order Table'));
        $this->switchFrame();
        $I->waitForElementVisible($this->getElement('Credit Card Last Name'));
        $I->fillField($this->getElement('Credit Card Last Name'), $data_field_values->last_name);
        $I->fillField($this->getElement('Credit Card Card number'), $data_field_values->card_number);
        $I->fillField($this->getElement('Credit Card CVV'), $data_field_values->cvv);
        $I->selectOption($this->getElement('Credit Card Valid until month'), $data_field_values->valid_until_month);
        $I->selectOption($this->getElement('Credit Card Valid until year'), $data_field_values->valid_until_year);
        $I->switchToIFrame();
        $I->wait(1);
    }

    /**
     * Method switchFrame
     * @since 1.4.0
     */
    public function switchFrame()
    {
        // Switch to Credit Card UI frame
        $I = $this->tester;
        //wait for Javascript to load iframe and it's contents
        $I->wait(10);
        //get wirecard seemless frame name
        $wirecard_frame_name = $I->executeJS('return document.querySelector(".wirecard-seamless-frame").getAttribute("name")');
        $I->switchToIFrame("$wirecard_frame_name");
    }
}
