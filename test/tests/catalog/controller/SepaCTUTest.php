<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/opencart-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/opencart-ee/blob/master/LICENSE
 */

use Mockery as m;

require_once __DIR__ . '/../../../../catalog/controller/extension/payment/wirecard_pg_sepact.php';


/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class SepaCTUTest extends \PHPUnit_Framework_TestCase
{
	protected $config;

	private $controller;
	private $loader;
	private $registry;
	private $session;
	private $response;
	private $modelOrder;
	private $url;
	private $language;
	private $cart;
	private $currency;
	private $customer;

	const SHOP = 'OpenCart';
	const PLUGIN = 'Wirecard OpenCart Extension';

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
            'currency_value' => 1.12,
            'customer_id' => 1,
            'payment_iso_code_2' => 'AT',
            'payment_zone_code' => 'OR',
            'payment_city' => 'BillingCity',
            'payment_address_1' => 'BillingStreet1',
            'payment_address_2' => 'BillingStreet2',
            'payment_postcode' => '0000',
            'payment_firstname' => 'Jon',
            'payment_lastname' => 'Doe',
            'telephone' => '000356788990',
            'shipping_iso_code_2' => 'AT',
            'shipping_zone_code' => 'OR',
            'shipping_city' => 'ShippingCity',
            'shipping_address_1' => 'ShippingStreet',
            'shipping_postcode' => '0000',
            'shipping_firstname' => 'Tina',
            'shipping_lastname' => 'Doe',
        );

		$this->modelOrder->method('getOrder')->willReturn($orderDetails);

		$this->url = $this->getMockBuilder(Url::class)->disableOriginalConstructor()->getMock();

		$this->currency = $this->getMockBuilder(Currency::class)->disableOriginalConstructor()->getMock();

		$this->loader = $this->getMockBuilder(Loader::class)
			->disableOriginalConstructor()
			->setMethods(['model', 'language', 'view'])
			->getMock();

		$this->language = $this->getMockBuilder(Language::class)->disableOriginalConstructor()->getMock();

		$this->customer = $this->getMockBuilder(Customer::class)
			->disableOriginalConstructor()
			->setMethods(['isLogged'])
			->getMock();

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
			null,
			$this->language,
			$this->cart,
			$this->currency,
			null,
			null,
			$this->customer
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
			null,
			$this->language,
			$this->cart,
			$this->currency,
			null,
			null,
			$this->customer
		);

		$expected = new \Wirecard\PaymentSdk\Config\Config('api-test.com', 'user', 'password');
		$expected->add(new \Wirecard\PaymentSdk\Config\SepaConfig(
		    'sepacredit',
			'account123',
			'secret123'
		));
		$expected->setShopInfo(self::SHOP, VERSION);
		$expected->setPluginInfo(self::PLUGIN, PLUGIN_VERSION);

		$currency = [
			'currency_code' => 'EUR',
			'currency_value' => 1.12
		];
		$actual = $this->controller->getConfig($currency);

		$this->assertEquals($expected, $actual);
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
			null,
			$this->language,
			$this->cart,
			$this->currency,
			null,
			null,
			$this->customer
		);

		$reflector = new ReflectionClass(ControllerExtensionPaymentWirecardPGSepaCT::class);
		$prop = $reflector->getProperty('transaction');
		$prop->setAccessible(true);

		$this->controller->confirm();

		$this->assertInstanceof(\Wirecard\PaymentSdk\Transaction\SepaCreditTransferTransaction::class, $prop->getValue($this->controller));
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
			null,
			$this->language,
			$this->cart,
			$this->currency,
			null,
			null,
			$this->customer
		);

		$actual = $this->controller->index();

		$this->assertNotNull($actual);
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
			null,
			$this->language,
			$this->cart,
			$this->currency,
			null,
			null,
			$this->customer
		);

		$reflector = new ReflectionClass(ControllerExtensionPaymentWirecardPGSepaCT::class);
		$prop = $reflector->getProperty('transaction');
		$prop->setAccessible(true);

		$transaction = array(
			'transaction_id' => '1234',
			'amount' => '10'
		);

		$expected = new \Wirecard\PaymentSdk\Transaction\SepaCreditTransferTransaction();
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
			null,
			$this->language,
			$this->cart,
			$this->currency,
			null,
			null,
			$this->customer
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
			null,
			$this->language,
			$this->cart,
			$this->currency,
			null,
			null,
			$this->customer
		);

		$expected = new \Wirecard\PaymentSdk\Transaction\SepaCreditTransferTransaction();

		$actual = $this->controller->getTransactionInstance();

		$this->assertEquals($expected, $actual);
	}
}
