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
    }


    /**
     * This test will be executed only in 'installator' environments
     *
     * @group installator
     * @since   1.4.0
     */

    public function tryToTest(AcceptanceTester $I)
    {
        $I->click('//*[@id="menu-extension"]');
        $I->waitForElementVisible('//*[@id="collapse2"]');
        $I->click('Extensions','//*[@id="collapse2"]');
        $I->wait(2);
        $I->selectOption('//*[@class="form-control"]', 'Modules');
        $I->wait(2);
        $I->scrollTo(['class' => 'container-fluid'], 0, 4000);
        $I->wait(2);
        $I->click('//*[@id="extension"]/fieldset/div[2]/table/tbody/tr[td//text()[contains(., \'Wirecard\')]]/td[3]/a');
    }
}
