<?php
/**
 * Shop System Extensions:
 * - Terms of Use can be found at:
 * https://github.com/wirecard/opencart-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/opencart-ee/blob/master/LICENSE
 */

use Helper\Acceptance;
use Page\Base;
use Page\Cart as CartPage;
use Page\Checkout as CheckoutPage;
use Page\Shop as ShopPage;
use Page\OrderReceived as OrderReceivedPage;
use Page\Verified as VerifiedPage;

class AcceptanceTester extends \Codeception\Actor
{

    use _generated\AcceptanceTesterActions;

    /**
     * @var string
     * @since 1.4.0
     */
    private $currentPage;

    /**
     * @var array
     * @since 1.5.0
     */
    private $mappedPaymentActions = [
        'creditcard' => [
            'config' => [
                'reserve' => 'reserve',
                'pay' => 'pay',
            ],
            'tx_table' => [
                'authorization' => 'authorization',
                'purchase' => 'purchase'
            ]
        ]
    ];

    /**
     * Method selectPage
     *
     * @param string $name
     * @return Base
     *
     * @since   1.4.0
     */
    private function selectPage($name)
    {
        switch ($name) {
            case 'Checkout':
                $this->wait(1);
                $page = new CheckoutPage($this);
                break;
            case 'Shop':
                $page = new ShopPage($this);
                break;
            case 'Cart':
                $page = new CartPage($this);
                break;
            case 'Verified':
                $this->wait(15);
                $page = new VerifiedPage($this);
                break;
            case 'Order Received':
                $this->wait(15);
                $page = new OrderReceivedPage($this);
                break;
            default:
                $page = null;
        }
        return $page;
    }

    /**
     * Method getPageElement
     *
     * @param string $elementName
     * @return string
     *
     * @since   1.4.0
     */
    private function getPageElement($elementName)
    {
        //Takes the required element by it's name from required page
        return $this->currentPage->getElement($elementName);
    }

    /**
     * @Given I am on :page page
     * @since 1.4.0
     */
    public function iAmOnPage($page)
    {
        // Open the page and initialize required pageObject
        $this->currentPage = $this->selectPage($page);
        $this->amOnPage($this->currentPage->getURL());
    }

    /**
     * @When I click :object
     * @since 1.4.0
     */
    public function iClick($object)
    {
        $this->waitForElementVisible($this->getPageElement($object));
        $this->waitForElementClickable($this->getPageElement($object));
        $this->click($this->getPageElement($object));
    }

    /**
     * @When I am redirected to :page page
     * @since 1.4.0
     */
    public function iAmRedirectedToPage($page)
    {
        // Initialize required pageObject WITHOUT checking URL
        $this->currentPage = $this->selectPage($page);
        // Check only specific keyword that page URL should contain
        $this->seeInCurrentUrl($this->currentPage->getURL());
    }

    /**
     * @When I fill fields with :data
     * @since 1.4.0
     */
    public function iFillFieldsWith($data)
    {
        $this->fillFieldsWithData($data, $this->currentPage);
    }

    /**
     * @When I enter :fieldValue in field :fieldID
     * @since 1.4.0
     */
    public function iEnterInField($fieldValue, $fieldID)
    {
        $this->waitForElementVisible($this->getPageElement($fieldID));
        $this->fillField($this->getPageElement($fieldID), $fieldValue);
    }

    /**
     * @Then I see :text
     * @since 1.4.0
     */
    public function iSee($text)
    {
        $this->see($text);
    }

    /**
     * @Given I prepare checkout
     * @since 1.4.0
     */
    public function iPrepareCheckout()
    {
        $this->iAmOnPage('Shop');
        //chose a product and open product page
        $this->click($this->currentPage->getElement('Currency'));
        $this->click($this->currentPage->getElement('EUR'));
        $this->click($this->currentPage->getElement('First Product in the Product List'));
        $this->iAmOnPage('Cart');
        $this->click('Checkout');
        $this->iAmRedirectedToPage('Checkout');
        $this->selectOption($this->currentPage->getElement('Checkout Options'), 'Guest Checkout');
        $this->click($this->currentPage->getElement('Continue1'));
    }

    /**
     * @When I check :box
     * @since 1.4.0
     */
    public function iCheck($box)
    {
        $this->checkOption($this->currentPage->getElement($box));
    }

    /**
     * @Given I activate :paymentMethod payment action :paymentAction in configuration
     * @param string $paymentMethod
     * @param string $paymentAction
     * @since 1.5.0
     */
    public function iActivatePaymentActionInConfiguration($paymentMethod, $paymentAction)
    {
        $this->updateInDatabase(
            'oc_setting',
            ['value' => $this->mappedPaymentActions[$paymentMethod]['config'][$paymentAction]],
            ['key' => 'payment_wirecard_pg_'.$paymentMethod.'_payment_action']
        );
    }
    /**
     * @Then I see :paymentMethod :paymentAction in transaction table
     * @param string $paymentMethod
     * @param string $paymentAction
     * @since 1.5.0
     */
    public function iSeeInTransactionTable($paymentMethod, $paymentAction)
    {
        # wait for transaction to appear in transaction table
        $this->wait(10);
        $this->seeInDatabase(
            'oc_wirecard_ee_transactions',
            ['transaction_type' => $this->mappedPaymentActions[$paymentMethod]['tx_table'][$paymentAction]]
        );
        //check that last transaction in the table is the one under test
        $transactionTypes = $this->getColumnFromDatabaseNoCriteria('oc_wirecard_ee_transactions', 'transaction_type');
        $this->assertEquals(end($transactionTypes), $this->mappedPaymentActions[$paymentMethod]['tx_table'][$paymentAction]);
    }
}
