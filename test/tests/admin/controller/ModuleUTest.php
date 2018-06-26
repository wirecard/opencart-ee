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
			$this->cart
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
			$this->cart
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
			$this->cart
		);

		$liveChatBlock = $this->controller->loadLiveChat(array());

		$this->assertArrayHasKey('live_chat', $liveChatBlock);
	}
}