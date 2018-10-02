<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/opencart-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/opencart-ee/blob/master/LICENSE
 */

use Mockery as m;

require_once __DIR__ . '/../../../../catalog/controller/extension/payment/wirecard_pg_maestro.php';
require_once __DIR__ . '/../../../../catalog/model/extension/payment/wirecard_pg_maestro.php';

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class MaestroUTest extends \PHPUnit_Framework_TestCase
{
	protected $config;
	private $pluginVersion = '1.2.0';
	private $controller;
	private $loader;
	private $registry;
	private $session;
	private $response;
	private $modelOrder;
	private $url;
	private $modelMaestro;
	private $language;
	private $cart;
	private $currency;
	private $customer;

	const SHOP = 'OpenCart';
	const PLUGIN = 'Wirecard OpenCart Extension';

	public function setUp() {
		$this->registry = $this->getMockBuilder(Registry::class)->disableOriginalConstructor()->getMock();

		$this->config = $this->getMockBuilder(Config::class)->disableOriginalConstructor()
			->setMethods(['get'])
			->getMock();

		$this->config->method('get')->willReturn('something');

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

		$this->currency = $this->getMockBuilder(Currency::class)->disableOriginalConstructor()->getMock();

		$this->customer = $this->getMockBuilder(Customer::class)
			->disableOriginalConstructor()
			->setMethods(['isLogged'])
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

		$this->modelMaestro = $this->getMockBuilder(ModelExtensionPaymentWirecardPGMaestro::class)
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

		$this->controller = new ControllerExtensionPaymentWirecardPGMaestro(
			$this->registry,
			$this->config,
			$this->loader,
			$this->session,
			$this->response,
			$this->modelOrder,
			$this->url,
			$this->modelMaestro,
			$this->language,
			$this->cart,
			$this->currency,
			null,
			null,
			$this->customer
		);
	}

	public function testIndexActive() {
		$this->config->expects($this->at(0))->method('get')->willReturn(1);
		$this->loader->method('view')->willReturn('active');
		$this->controller = new ControllerExtensionPaymentWirecardPGMaestro(
			$this->registry,
			$this->config,
			$this->loader,
			$this->session,
			$this->response,
			$this->modelOrder,
			$this->url,
			$this->modelMaestro,
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

	public function testSuccessConfirm() {
        $orderManager = m::mock('overload:PGOrderManager');
        $orderManager->shouldReceive('createResponseOrder');

		$this->controller->request->post = array (
			'merchant_account_id' => '1111111111',
			'transaction_id' => 'da04876d-1d92-431c-b33a-49080914c996',
			'transaction_type' => 'authorization',
			'payment_method' => 'creditcard',
			'request_id' => '123',
			'transaction_state' => 'success',
			'status_code_1' => '201.0000',
			'status_description_1' => '3d-acquirer:The resource was successfully created.',
			'status_severity_1' => 'information'
		);

        $this->controller->confirm();
		$json['response'] = [];
		$this->response->method('getOutput')->willReturn(json_encode($json));

		$expected = json_encode($json);

		$this->assertEquals($expected, $this->response->getOutput());
	}

	public function testGetConfig() {
		$config = $this->getMockBuilder(Config::class)->disableOriginalConstructor()->getMock();
		$config->expects($this->at(0))->method('get')->willReturn('api-test.com');
		$config->expects($this->at(1))->method('get')->willReturn('user');
		$config->expects($this->at(2))->method('get')->willReturn('password');

		$this->controller = new ControllerExtensionPaymentWirecardPGMaestro(
			$this->registry,
			$config,
			$this->loader,
			$this->session,
			$this->response,
			$this->modelOrder,
			$this->url,
			$this->modelMaestro,
			$this->language,
			$this->cart,
			$this->currency,
			null,
			null,
			$this->customer
		);

		$expected = new \Wirecard\PaymentSdk\Config\Config('api-test.com', 'user', 'password');
		$paymentConfig = new \Wirecard\PaymentSdk\Config\MaestroConfig(
			'account123',
			'secret123'
		);

		$expected->add($paymentConfig);
		$expected->setShopInfo(self::SHOP, VERSION);
		$expected->setPluginInfo(self::PLUGIN, $this->pluginVersion);

		$currency = [
			'currency_code' => 'EUR',
			'currency_value' => 1
		];
		$actual = $this->controller->getConfig($currency);
		$this->assertEquals($expected, $actual);
	}

	public function testGetModel() {
		$actual = $this->controller->getModel();

		$this->assertInstanceOf(get_class($this->modelMaestro), $actual);
	}

	public function testGetMaestroUiRequestData() {
        $actual = $this->controller;
        $transactionService = m::mock('overload:TransactionService');
        $transactionService->shouldReceive('getCreditCardUiWithData');
        $actual->getMaestroUiRequestData();

        $this->assertNull($actual->response->getOutput());
    }

    public function testGetPaymentAction() {
	    $actual = $this->controller->getPaymentAction('pay');
	    $this->assertEquals('purchase', $actual);

	    $actual = $this->controller->getPaymentAction('authorization');
		$this->assertEquals('authorization', $actual);
    }

    public function testGetTransactionInstance() {
		$this->assertTrue($this->controller->getTransactionInstance() instanceof \Wirecard\PaymentSdk\Transaction\MaestroTransaction);
    }

    public function testCreateTransaction() {
		$expected = new \Wirecard\PaymentSdk\Transaction\MaestroTransaction();
		$expected->setParentTransactionId('asd');
		$expected->setAmount(new \Wirecard\PaymentSdk\Entity\Amount(20, 'EUR'));
		$actual = $this->controller->createTransaction(['transaction_id' => 'asd'], new \Wirecard\PaymentSdk\Entity\Amount(20, 'EUR'));

		$this->assertEquals($expected, $actual);
    }

    public function testGetController() {
		$expected = $this->controller->getController('creditcard');
		$this->assertNotNull($expected);
    }
}