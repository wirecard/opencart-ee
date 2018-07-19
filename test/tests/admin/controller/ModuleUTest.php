<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/opencart-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/opencart-ee/blob/master/LICENSE
 */

require_once DIR_ADMIN . 'controller/extension/module/wirecard_pg.php';

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class ModuleUTest extends \PHPUnit_Framework_TestCase
{
	protected $config;
	private $pluginVersion = '1.0.0';
	private $controller;
	private $loader;
	private $registry;
	private $session;
	private $response;
	private $url;
	private $language;
	private $cart;
	private $currency;

	const SHOP = 'OpenCart';
	const PLUGIN = 'Wirecard_PaymentGateway';

	public function setUp()
	{
		$this->registry = $this->getMockBuilder(Registry::class)
			->disableOriginalConstructor()
			->getMock();

		$this->config = $this->getMockBuilder(Config::class)
			->disableOriginalConstructor()
			->setMethods(['get'])
			->getMock();

		$this->config->method('get')
			->willReturn('somthing');

		$this->session = $this->getMockBuilder(Session::class)
			->disableOriginalConstructor()
			->getMock();

		$this->session->data['user_token'] = '12345';

		$this->response = $this->getMockBuilder(Response::class)
			->disableOriginalConstructor()
			->setMethods(['addHeader', 'setOutput', 'getOutput', 'redirect'])
			->getMock();

		$this->cart = $this->getMockBuilder(Cart::class)
			->disableOriginalConstructor()
			->setMethods(['getProducts'])
			->getMock();

		$this->url = $this->getMockBuilder(Url::class)
			->disableOriginalConstructor()
			->getMock();

		$this->loader = $this->getMockBuilder(Loader::class)
			->disableOriginalConstructor()
			->setMethods(['model', 'language', 'view', 'controller'])
			->getMock();

		$this->language = $this->getMockBuilder(Language::class)
			->disableOriginalConstructor()
			->getMock();

		$items = [
			["price" => 10.465, "name" => "Produkt1", "quantity" => 2, "product_id" => 2, "tax_class_id" => 2],
			["price" => 20.241, "name" => "Produkt2", "quantity" => 3, "product_id" => 1, "tax_class_id" => 1],
			["price" => 3.241, "name" => "Produkt3", "quantity" => 5, "product_id" => 3, "tax_class_id" => 1]
		];

		$this->cart->method('getProducts')->willReturn($items);

		$this->currency = $this->getMockBuilder(Currency::class)->disableOriginalConstructor()->getMock();

		$this->controller = new ControllerExtensionModuleWirecardPG(
			$this->registry,
			$this->config,
			$this->loader,
			$this->session,
			$this->response,
			null,
			$this->url,
			null,
			$this->language,
			$this->cart,
			$this->currency
		);
	}

	public function testGetCommonTemplateBlocks()
	{
		$this->controller = new ControllerExtensionModuleWirecardPG(
			$this->registry,
			$this->config,
			$this->loader,
			$this->session,
			$this->response,
			null,
			$this->url,
			null,
			$this->language,
			$this->cart,
			$this->currency
		);

		$commonsData = $this->controller->getCommons();

		$this->assertArrayHasKey('user_token', $commonsData);
		$this->assertArrayHasKey('live_chat', $commonsData);
		$this->assertArrayHasKey('header', $commonsData);
		$this->assertArrayHasKey('column_left', $commonsData);
		$this->assertArrayHasKey('footer', $commonsData);
	}

	public function testGetLiveChatModule()
	{
		// Local require here because we get naming conflicts otherwise.
		require_once DIR_ADMIN . 'controller/extension/payment/wirecard_pg_paypal.php';

		$this->controller = new ControllerExtensionPaymentWirecardPGPayPal(
			$this->registry,
			$this->config,
			$this->loader,
			$this->session,
			$this->response,
			null,
			$this->url,
			null,
			$this->language,
			$this->cart,
			$this->currency
		);

		$liveChatBlock = $this->controller->loadLiveChat(array());

		$this->assertArrayHasKey('live_chat', $liveChatBlock);
	}
}