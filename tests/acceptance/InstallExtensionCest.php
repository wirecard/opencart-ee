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


class InstallExtensionCest
{

    private $userToken = '';

    public function _before(\AcceptanceTester $I)
    {
        $I->amOnPage('/admin');
        $email = getenv('OPENCART_USERNAME');
        $password = getenv('OPENCART_PASSWORD');
        $I->submitForm("//*[@method='post']", [
            'username' => $email,
            'password' => $password
        ]);
        $I->see('Dashboard');
        //get user token to use it for extension installation
        $this->userToken = $I->grabFromCurrentUrl('/user_token=(\w+)/i');
    }


    /**
     * This test will be executed only in 'installator' environments
     *
     * @group installator
     * @since   1.4.0
     */

    public function tryToTest(AcceptanceTester $I)
    {
        //send GET request to emulate pressing "Install" button
        $I->amOnPage('/admin/index.php?route=extension/extension/module/install&user_token=' . $this->userToken .'&extension=wirecard_pg');
        // make sure payment method is installed
        $I->amOnPage('/admin/index.php?route=extension/payment/wirecard_pg_creditcard&user_token=' . $this->userToken );
        $I->click('//*[@class="fa fa-save"]');
    }
}
