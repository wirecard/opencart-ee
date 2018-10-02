<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/opencart-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/opencart-ee/blob/master/LICENSE
 */

require_once DIR_ADMIN . 'controller/extension/payment/wirecard_pg/language_helper.php';

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class LanguageHelperUTest extends \PHPUnit_Framework_TestCase
{
	protected $config;
	private $pluginVersion = '1.2.0';
	private $controller;
	private $loader;
	private $registry;
	private $session;
	private $response;
	private $url;
	private $language;
	private $cart;
	private $currency;
	private $modelLocalization;

	const SHOP = 'OpenCart';
	const PLUGIN = 'Wirecard OpenCart Extension';

	public function setUp()
	{
		$this->registry = $this->getMockBuilder(Registry::class)
			->disableOriginalConstructor()
			->getMock();

		$this->config = $this->getMockBuilder(Config::class)
			->disableOriginalConstructor()
			->setMethods(['get'])
			->getMock();

		$this->config->expects($this->at(0))->method('get')->willReturn(1);
		$this->config->expects($this->at(1))->method('get')->willReturn(2);

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

		$this->modelLocalization = $this->getMockBuilder(ModelLocalisationLanguage::class)
			->disableOriginalConstructor()
			->setMethods(['getLanguages'])
			->getMock();

		$this->modelLocalization->method('getLanguages')->willReturn([
			[ 'language_id' => 1, 'code' => 'de-de' ],
			[ 'language_id' => 2, 'code' => 'en-gb' ],
		]);

		$items = [
			["price" => 10.465, "name" => "Produkt1", "quantity" => 2, "product_id" => 2, "tax_class_id" => 2],
			["price" => 20.241, "name" => "Produkt2", "quantity" => 3, "product_id" => 1, "tax_class_id" => 1],
			["price" => 3.241, "name" => "Produkt3", "quantity" => 5, "product_id" => 3, "tax_class_id" => 1]
		];

		$this->cart->method('getProducts')->willReturn($items);

		$this->currency = $this->getMockBuilder(Currency::class)->disableOriginalConstructor()->getMock();

		$this->controller = new ControllerExtensionPaymentWirecardPGLanguageHelper(
			$this->registry,
			$this->config,
			$this->loader,
			$this->session,
			$this->response,
			null,
			$this->url,
			$this->modelLocalization,
			$this->language,
			$this->cart,
			$this->currency
		);
	}

	public function testGetActiveLanguage() {
		$activeLanguage = $this->controller->getActiveLanguageCode();
		$this->assertEquals('de_de', $activeLanguage);

		$activeLanguage = $this->controller->getActiveLanguageCode();
		$this->assertEquals('en_gb', $activeLanguage);
	}
}