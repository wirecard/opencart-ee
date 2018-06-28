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
 */

use Mockery as m;

require_once __DIR__ . '/../../../../catalog/controller/extension/payment/wirecard_pg_sepact.php';
require_once __DIR__ . '/../../../../catalog/model/extension/payment/wirecard_pg_sepact.php';

use Wirecard\PaymentSdk\Transaction\SepaTransaction;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class SepaCTUTest extends \PHPUnit_Framework_TestCase
{
	protected $config;
	private $pluginVersion = '1.0.0';
	private $controller;
	private $loader;
	private $registry;
	private $session;
	private $response;
	private $modelOrder;
	private $url;
	private $modelSepaCT;
	private $language;
	private $cart;

	const SHOP = 'OpenCart';
	const PLUGIN = 'Wirecard_PaymentGateway';

	public function setUp()
	{
		$this->registry = $this->getMockBuilder(Registry::class)->disableOriginalConstructor()->getMock();

		$this->config = $this->getMockBuilder(Config::class)
			->disableOriginalConstructor()
			->setMethods(['get'])
			->getMock();

		$this->config->method('get')->willReturn('somthing');

		$this->session = $this->getMockBuilder(Session::class)->disableOriginalConstructor()->getMock();

		$this->response = $this->getMockBuilder(Response::class)
			->disableOriginalConstructor()
			->setMethods(['addHeader', 'setOutput', 'getOutput', 'redirect'])
			->getMock();

		$this->modelOrder = $this->getMockBuilder(ModelCheckoutOrder::class)
			->disableOriginalConstructor()
			->setMethods(['getOrder', 'addOrderHistory'])
			->getMock();

		$this->cart = $this->getMockBuilder(Cart::class)
			->disableOriginalConstructor()
			->setMethods(['getProducts'])
			->getMock();

		$orderDetails = array(
			'order_id' => '1',
			'total' => '20',
			'currency_code' => 'EUR',
			'language_code' => 'en-GB',
			'email' => 'test@test.com',
			'firstname' => 'Jon',
			'lastname' => 'Doe',
			'ip' => '1',
			'store_name' => 'Demoshop',
			'currency_value' => 1,
			'customer_id' => 1,
			'payment_iso_code_2' => 'AT',
			'payment_city' => 'BillingCity',
			'payment_address_1' => 'BillingStreet1',
			'payment_address_2' => 'BillingStreet2',
			'payment_postcode' => '0000',
			'payment_firstname' => 'Jon',
			'payment_lastname' => 'Doe',
			'telephone' => '000356788990',
			'shipping_iso_code_2' => 'AT',
			'shipping_city' => 'ShippingCity',
			'shipping_address_1' => 'ShippingStreet',
			'shipping_postcode' => '0000',
			'shipping_firstname' => 'Tina',
			'shipping_lastname' => 'Doe',
		);

		$this->modelOrder->method('getOrder')->willReturn($orderDetails);

		$this->url = $this->getMockBuilder(Url::class)->disableOriginalConstructor()->getMock();

		$this->modelSepaCT = $this->getMockBuilder(ModelExtensionPaymentWirecardPGSepaCT::class)
			->disableOriginalConstructor()
			->setMethods(['sendRequest'])
			->getMock();

		$this->loader = $this->getMockBuilder(Loader::class)
			->disableOriginalConstructor()
			->setMethods(['model', 'language', 'view'])
			->getMock();

		$this->language = $this->getMockBuilder(Language::class)->disableOriginalConstructor()->getMock();

		$items = [
			["price" => 10.465, "name" => "Produkt1", "quantity" => 2, "product_id" => 2, "tax_class_id" => 2],
			["price" => 20.241, "name" => "Produkt2", "quantity" => 3, "product_id" => 1, "tax_class_id" => 1],
			["price" => 3.241, "name" => "Produkt3", "quantity" => 5, "product_id" => 3, "tax_class_id" => 1]
		];

		$this->cart->method('getProducts')->willReturn($items);

		$this->controller = new ControllerExtensionPaymentWirecardPGSepaCT(
			$this->registry,
			$this->config,
			$this->loader,
			$this->session,
			$this->response,
			$this->modelOrder,
			$this->url,
			$this->modelSepaCT,
			$this->language,
			$this->cart
		);
	}

	public function testGetConfig()
	{
		$config = $this->getMockBuilder(Config::class)->disableOriginalConstructor()->getMock();
		$config->expects($this->at(0))->method('get')->willReturn('account123');
		$config->expects($this->at(1))->method('get')->willReturn('secret123');
		$config->expects($this->at(2))->method('get')->willReturn('api-test.com');
		$config->expects($this->at(3))->method('get')->willReturn('user');
		$config->expects($this->at(4))->method('get')->willReturn('password');

		$this->controller = new ControllerExtensionPaymentWirecardPGSepaCT(
			$this->registry,
			$config,
			$this->loader,
			$this->session,
			$this->response,
			$this->modelOrder,
			$this->url,
			$this->modelSepaCT,
			$this->language,
			$this->cart
		);

		$expected = new \Wirecard\PaymentSdk\Config\Config('api-test.com', 'user', 'password');
		$expected->add(new \Wirecard\PaymentSdk\Config\SepaConfig(
			'account123',
			'secret123'
		));
		$expected->setShopInfo(self::SHOP, VERSION);
		$expected->setPluginInfo(self::PLUGIN, $this->pluginVersion);

		$currency = [
			'currency_code' => 'EUR',
			'currency_value' => 1
		];
		$actual = $this->controller->getConfig($currency);

		$this->assertEquals($expected, $actual);
	}

	public function testGetModel()
	{
		$actual = $this->controller->getModel();

		$this->assertInstanceOf(get_class($this->modelSepaCT), $actual);
	}


	public function testConfirm()
	{
		$this->controller = new ControllerExtensionPaymentWirecardPGSepaCT(
			$this->registry,
			$this->config,
			$this->loader,
			$this->session,
			$this->response,
			$this->modelOrder,
			$this->url,
			$this->modelSepaCT,
			$this->language,
			$this->cart
		);

		$reflector = new ReflectionClass(ControllerExtensionPaymentWirecardPGSepaCT::class);
		$prop = $reflector->getProperty('transaction');
		$prop->setAccessible(true);

		$this->controller->confirm();

		$this->assertInstanceof(SepaTransaction::class, $prop->getValue($this->controller));
	}

	public function testIndexActive()
	{
		$this->config->expects($this->at(0))->method('get')->willReturn(1);
		$this->loader->method('view')->willReturn('active');
		$this->controller = new ControllerExtensionPaymentWirecardPGSepaCT(
			$this->registry,
			$this->config,
			$this->loader,
			$this->session,
			$this->response,
			$this->modelOrder,
			$this->url,
			$this->modelSepaCT,
			$this->language,
			$this->cart
		);

		$actual = $this->controller->index();

		$this->assertNotNull($actual);
	}

	public function testPaymentAction()
	{
		$this->controller = new ControllerExtensionPaymentWirecardPGSepaCT(
			$this->registry,
			$this->config,
			$this->loader,
			$this->session,
			$this->response,
			$this->modelOrder,
			$this->url,
			$this->modelSepaCT,
			$this->language,
			$this->cart
		);

		$actual = $this->controller->getPaymentAction('pay');
		$this->assertEquals('debit', $actual);
	}

	public function testCreateTransaction()
	{
		$this->controller = new ControllerExtensionPaymentWirecardPGSepaCT(
			$this->registry,
			$this->config,
			$this->loader,
			$this->session,
			$this->response,
			$this->modelOrder,
			$this->url,
			$this->modelSepaCT,
			$this->language,
			$this->cart
		);

		$reflector = new ReflectionClass(ControllerExtensionPaymentWirecardPGSepaCT::class);
		$prop = $reflector->getProperty('transaction');
		$prop->setAccessible(true);

		$transaction = array(
			'transaction_id' => '1234',
			'amount' => '10'
		);

		$expected = new SepaTransaction();
		$expected->setParentTransactionId('1234');

		$actual = $this->controller->createTransaction($transaction, null);

		$this->assertEquals($expected, $actual);
	}

	public function testGetType()
	{
		$this->controller = new ControllerExtensionPaymentWirecardPGSepaCT(
			$this->registry,
			$this->config,
			$this->loader,
			$this->session,
			$this->response,
			$this->modelOrder,
			$this->url,
			$this->modelSepaCT,
			$this->language,
			$this->cart
		);

		$actual = $this->controller->getType();
		$expected = 'sepact';

		$this->assertEquals($expected, $actual);
	}

	public function testGetInstance()
	{
		$this->controller = new ControllerExtensionPaymentWirecardPGSepaCT(
			$this->registry,
			$this->config,
			$this->loader,
			$this->session,
			$this->response,
			$this->modelOrder,
			$this->url,
			$this->modelSepaCT,
			$this->language,
			$this->cart
		);

		$expected = new \Wirecard\PaymentSdk\Transaction\SepaTransaction();

		$actual = $this->controller->getTransactionInstance();

		$this->assertEquals($expected, $actual);
	}
}
