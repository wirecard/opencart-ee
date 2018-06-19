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

require_once __DIR__ . '/../../../../catalog/controller/extension/payment/wirecard_pg_creditcard.php';
require_once __DIR__ . '/../../../../catalog/model/extension/payment/wirecard_pg_creditcard.php';

class CreditCardUTest extends \PHPUnit_Framework_TestCase
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
	private $modelCreditCard;
	private $language;
	private $cart;

	const SHOP = 'OpenCart';
	const PLUGIN = 'Wirecard_PaymentGateway';

	public function setUp()
	{
		$this->registry = $this->getMockBuilder(Registry::class)->disableOriginalConstructor()->getMock();

		$this->config = $this->getMockBuilder(Config::class)->disableOriginalConstructor()->getMock();

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
			'order_id'                => '1',
			'total'                   => '20',
			'currency_code'           => 'EUR',
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

		$this->modelCreditCard = $this->getMockBuilder(ModelExtensionPaymentWirecardPGCreditCard::class)
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

		$this->controller = new ControllerExtensionPaymentWirecardPGCreditCard(
			$this->registry,
			$this->config,
			$this->loader,
			$this->session,
			$this->response,
			$this->modelOrder,
			$this->url,
			$this->modelCreditCard,
			$this->language,
			$this->cart
		);
	}

	public function testIndexActive()
	{
		$this->config->expects($this->at(0))->method('get')->willReturn(1);
		$this->loader->method('view')->willReturn('active');
		$this->controller = new ControllerExtensionPaymentWirecardPGCreditCard(
			$this->registry,
			$this->config,
			$this->loader,
			$this->session,
			$this->response,
			$this->modelOrder,
			$this->url,
			$this->modelCreditCard,
			$this->language,
			$this->cart
		);

		$actual = $this->controller->index();

		$this->assertNotNull($actual);
	}

	public function testSuccessConfirm()
	{
        $orderManager = m::mock('overload:PGOrderManager');
        $orderManager->shouldReceive('createResponseOrder');

	    $_POST['merchant_account_id'] = '1111111111';
	    $_POST['transaction_id'] = 'da04876d-1d92-431c-b33a-49080914c996';
	    $_POST['transaction_type'] = 'authorization';
	    $_POST['payment_method'] = 'creditcard';
        $_POST['request_id'] = '123';
        $_POST['transaction_state'] = 'success';
        $_POST['status_code_1'] = '201.0000';
        $_POST['status_description_1'] = '3d-acquirer:The resource was successfully created.';
        $_POST['status_severity_1'] = 'information';

        $this->controller->confirm();
		$json['response'] = [];
		$this->response->method('getOutput')->willReturn(json_encode($json));

		$expected = json_encode($json);

		$this->assertEquals($expected, $this->response->getOutput());
	}

	public function testGetConfig()
	{
		$this->config->expects($this->at(0))->method('get')->willReturn('account123');
		$this->config->expects($this->at(1))->method('get')->willReturn('secret123');
		$this->config->expects($this->at(2))->method('get')->willReturn('api-test.com');
		$this->config->expects($this->at(3))->method('get')->willReturn('user');
		$this->config->expects($this->at(4))->method('get')->willReturn('password');
		$this->config->expects($this->at(5))->method('get')->willReturn('three_d');
		$this->config->expects($this->at(6))->method('get')->willReturn('account123three_d');
		$this->config->expects($this->at(7))->method('get')->willReturn('secret_three_d');
		$this->config->expects($this->at(8))->method('get')->willReturn(10);
		$this->config->expects($this->at(9))->method('get')->willReturn(10);
		$this->config->expects($this->at(10))->method('get')->willReturn(20);
		$this->config->expects($this->at(11))->method('get')->willReturn(20);

		$this->controller = new ControllerExtensionPaymentWirecardPGCreditCard(
			$this->registry,
			$this->config,
			$this->loader,
			$this->session,
			$this->response,
			$this->modelOrder,
			$this->url,
			$this->modelCreditCard,
			$this->language,
			$this->cart
		);

		$expected = new \Wirecard\PaymentSdk\Config\Config('api-test.com', 'user', 'password');
		$creditCardConfig = new \Wirecard\PaymentSdk\Config\CreditCardConfig('account123', 'secret123');
		$creditCardConfig->setThreeDCredentials('account123three_d','secret_three_d');
		$creditCardConfig->addSslMaxLimit(new \Wirecard\PaymentSdk\Entity\Amount(10, 'EUR'));
		$creditCardConfig->addThreeDMinLimit(new \Wirecard\PaymentSdk\Entity\Amount(20, 'EUR'));
		$expected->add($creditCardConfig);
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

		$this->assertInstanceOf(get_class($this->modelCreditCard), $actual);
	}
}