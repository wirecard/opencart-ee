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

require_once __DIR__ . '/../../../../catalog/controller/extension/payment/wirecard_pg_poi.php';
require_once __DIR__ . '/../../../../catalog/model/extension/payment/wirecard_pg_poi.php';

use Wirecard\PaymentSdk\Transaction\PoiPiaTransaction;
use Wirecard\PaymentSdk\Entity\AccountHolder;
use Wirecard\PaymentSdk\Entity\Address;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class PoiUTest extends \PHPUnit_Framework_TestCase
{
	protected $config;

	private $controller;
	private $loader;
	private $registry;
	private $session;
	private $response;
	private $modelOrder;
	private $url;
	private $modelPoi;
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
            'comment' => '',
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

		$this->modelPoi = $this->getMockBuilder(ModelExtensionPaymentWirecardPGPoi::class)
			->disableOriginalConstructor()
			->setMethods(['sendRequest'])
			->getMock();

		$this->url = $this->getMockBuilder(Url::class)->disableOriginalConstructor()->getMock();

		$this->loader = $this->getMockBuilder(Loader::class)
			->disableOriginalConstructor()
			->setMethods(['model', 'language', 'view'])
			->getMock();

		$this->language = $this->getMockBuilder(Language::class)->disableOriginalConstructor()->getMock();

		$this->currency = $this->getMockBuilder(Currency::class)->disableOriginalConstructor()->getMock();

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

		$this->controller = new ControllerExtensionPaymentWirecardPGPoi(
			$this->registry,
			$this->config,
			$this->loader,
			$this->session,
			$this->response,
			$this->modelOrder,
			$this->url,
			$this->modelPoi,
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

		$this->controller = new ControllerExtensionPaymentWirecardPGPoi(
			$this->registry,
			$config,
			$this->loader,
			$this->session,
			$this->response,
			$this->modelOrder,
			$this->url,
			$this->modelPoi,
			$this->language,
			$this->cart,
			$this->currency,
			null,
			null,
			$this->customer
		);

		$expected = new \Wirecard\PaymentSdk\Config\Config('api-test.com', 'user', 'password');
		$expected->add(new \Wirecard\PaymentSdk\Config\PaymentMethodConfig(
			PoiPiaTransaction::NAME,
			'account123',
			'secret123'
		));
		$expected->setShopInfo(self::SHOP, VERSION);
		$expected->setPluginInfo(self::PLUGIN, PLUGIN_VERSION);

		$currency = [
			'currency_code' => 'EUR',
			'currency_value' => 1
		];
		$actual = $this->controller->getConfig($currency);

		$this->assertEquals($expected, $actual);
	}

	public function testConfirm()
	{
		$this->controller = new ControllerExtensionPaymentWirecardPGPoi(
			$this->registry,
			$this->config,
			$this->loader,
			$this->session,
			$this->response,
			$this->modelOrder,
			$this->url,
			$this->modelPoi,
			$this->language,
			$this->cart,
			$this->currency,
			null,
			null,
			$this->customer
		);

		$reflector = new ReflectionClass(ControllerExtensionPaymentWirecardPGPoi::class);
		$prop = $reflector->getProperty('transaction');
		$prop->setAccessible(true);

		$this->controller->confirm();

		$this->assertInstanceof(PoiPiaTransaction::class, $prop->getValue($this->controller));
	}

	public function testIndexActive()
	{
		$this->config->expects($this->at(0))->method('get')->willReturn(1);
		$this->loader->method('view')->willReturn('active');
		$this->controller = new ControllerExtensionPaymentWirecardPGPoi(
			$this->registry,
			$this->config,
			$this->loader,
			$this->session,
			$this->response,
			$this->modelOrder,
			$this->url,
			$this->modelPoi,
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
		$this->controller = new ControllerExtensionPaymentWirecardPGPoi(
			$this->registry,
			$this->config,
			$this->loader,
			$this->session,
			$this->response,
			$this->modelOrder,
			$this->url,
			$this->modelPoi,
			$this->language,
			$this->cart,
			$this->currency,
			null,
			null,
			$this->customer
		);

		$transaction = array(
			'transaction_id' => '1234',
			'amount' => '10'
		);

		$expected = new PoiPiaTransaction();
		$expected->setParentTransactionId('1234');

		$actual = $this->controller->createTransaction($transaction, null);

		$this->assertEquals($expected, $actual);
	}

	public function testPrepareTransaction()
	{
		$this->controller = new ControllerExtensionPaymentWirecardPGPoi(
			$this->registry,
			$this->config,
			$this->loader,
			$this->session,
			$this->response,
			$this->modelOrder,
			$this->url,
			$this->modelPoi,
			$this->language,
			$this->cart,
			$this->currency,
			null,
			null,
			$this->customer
		);

		$order = $this->controller->model_checkout_order->getOrder('11111');

		$address = new Address($order['payment_iso_code_2'], $order['payment_city'], $order['payment_address_1']);
		$address->setPostalCode($order['payment_postcode']);
		$address->setStreet2($order['payment_address_2']);
		$address->setState('OR');

		$accountHolder = new AccountHolder();
		$accountHolder->setAddress($address);
		$accountHolder->setFirstName($order['payment_firstname']);
		$accountHolder->setLastName($order['payment_lastname']);
		$accountHolder->setEmail($order['email']);
		$accountHolder->setPhone($order['telephone']);

		$expected = new PoiPiaTransaction();
		$expected->setParentTransactionId('1234');
		$expected->setAccountHolder($accountHolder);

		$transaction = array(
			'transaction_id' => '1234',
			'amount' => '10'
		);

		$reflector = new ReflectionClass(ControllerExtensionPaymentWirecardPGPoi::class);
		$prop = $reflector->getProperty('transaction');
		$prop->setAccessible(true);

		$this->controller->createTransaction($transaction, null);
		$this->controller->prepareTransaction();

		/** @var PoiPiaTransaction $actual */
		$actual = $prop->getValue($this->controller);

		$this->assertEquals($expected->getAccountHolder(), $actual->getAccountHolder());
	}

	public function testAddBankDetailsToInvoice()
	{
		$this->controller = new ControllerExtensionPaymentWirecardPGPoi(
			$this->registry,
			$this->config,
			$this->loader,
			$this->session,
			$this->response,
			$this->modelOrder,
			$this->url,
			$this->modelPoi,
			$this->language,
			$this->cart,
			$this->currency,
			null,
			null,
			$this->customer
		);

		$config = new \Wirecard\PaymentSdk\Config\Config('api-test.com', 'user', 'password');
		$config->add(new \Wirecard\PaymentSdk\Config\PaymentMethodConfig(
			PoiPiaTransaction::NAME,
			'account123',
			'secret123'
		));
		$config->setShopInfo(self::SHOP, VERSION);
		$config->setPluginInfo(self::PLUGIN, PLUGIN_VERSION);

		$responseMapper = new \Wirecard\PaymentSdk\Mapper\ResponseMapper($config);
		$response = $responseMapper->map(ResponseProvider::getPOIResponse(), null);

		$bankDetails = $this->controller->addBankDetailsToInvoice($response, '1111', 'success');

		$this->assertEquals('DE11512308001234567890', $bankDetails['transaction']['iban']);
		$this->assertEquals('WIREDEMMXXX', $bankDetails['transaction']['bic']);
		$this->assertNotNull($bankDetails['transaction']['ptrid']);
	}

	public function testGetType()
	{
		$this->controller = new ControllerExtensionPaymentWirecardPGPoi(
			$this->registry,
			$this->config,
			$this->loader,
			$this->session,
			$this->response,
			$this->modelOrder,
			$this->url,
			$this->modelPoi,
			$this->language,
			$this->cart,
			$this->currency,
			null,
			null,
			$this->customer
		);

		$actual = $this->controller->getType();
		$expected = 'poi';

		$this->assertEquals($expected, $actual);
	}

	public function testGetInstance()
	{
		$this->controller = new ControllerExtensionPaymentWirecardPGPoi(
			$this->registry,
			$this->config,
			$this->loader,
			$this->session,
			$this->response,
			$this->modelOrder,
			$this->url,
			$this->modelPoi,
			$this->language,
			$this->cart,
			$this->currency,
			null,
			null,
			$this->customer
		);

		$expected = new \Wirecard\PaymentSdk\Transaction\PoiPiaTransaction();

		$actual = $this->controller->getTransactionInstance();

		$this->assertEquals($expected, $actual);
	}

	public function testGetModel()
	{
		$this->controller = new ControllerExtensionPaymentWirecardPGPoi(
			$this->registry,
			$this->config,
			$this->loader,
			$this->session,
			$this->response,
			$this->modelOrder,
			$this->url,
			$this->modelPoi,
			$this->language,
			$this->cart,
			$this->currency,
			null,
			null,
			$this->customer
		);

		$actual = $this->controller->getModel();

		$this->assertInstanceOf(get_class($this->modelPoi), $actual);
	}

	public function testCancelResponse()
	{
		$orderManager = m::mock('overload:PGOrderManager');
		$orderManager->shouldReceive('updateCancelFailureOrder');

		$_REQUEST = [
			'cancelled' => 1,
			'orderId' => 123
		];

		$this->controller = new ControllerExtensionPaymentWirecardPGPoi(
			$this->registry,
			$this->config,
			$this->loader,
			$this->session,
			$this->response,
			$this->modelOrder,
			$this->url,
			$this->modelPoi,
			$this->language,
			$this->cart,
			$this->currency,
			null,
			null,
			$this->customer
		);

		$actual = $this->controller->response();
		$this->assertNull($actual);
	}
}
