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

require_once __DIR__ . '/../../../../catalog/controller/extension/payment/wirecard_pg_paypal.php';
require_once __DIR__ . '/../../../../catalog/model/extension/payment/wirecard_pg_paypal.php';

class PayPalUTest extends \PHPUnit_Framework_TestCase
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
    private $modelPaypal;
    private $language;

    const SHOP = 'OpenCart';
    const PLUGIN = 'Wirecard_PaymentGateway';

    public function setUp()
    {
        $this->registry = $this->getMockBuilder(Registry::class)->disableOriginalConstructor()->getMock();

        $this->config = $this->getMockBuilder(Config::class)->disableOriginalConstructor()->getMock();

        $this->session = $this->getMockBuilder(Session::class)->disableOriginalConstructor()->getMock();

        $this->response = $this->getMockBuilder(Response::class)
            ->disableOriginalConstructor()
            ->setMethods(['addHeader', 'setOutput', 'getOutput'])
            ->getMock();

        $this->modelOrder = $this->getMockBuilder(ModelCheckoutOrder::class)
            ->disableOriginalConstructor()
            ->setMethods(['getOrder'])
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
        );
        $this->modelOrder->method('getOrder')->willReturn($orderDetails);

        $this->url = $this->getMockBuilder(Url::class)->disableOriginalConstructor()->getMock();

        $this->modelPaypal = $this->getMockBuilder(ModelExtensionPaymentWirecardPGPayPal::class)
            ->disableOriginalConstructor()
            ->setMethods(['sendRequest'])
            ->getMock();

        $this->loader = $this->getMockBuilder(Loader::class)
            ->disableOriginalConstructor()
            ->setMethods(['model', 'language', 'view'])
            ->getMock();

        $this->language = $this->getMockBuilder(Language::class)->disableOriginalConstructor()->getMock();

        $this->controller = new ControllerExtensionPaymentWirecardPGPayPal(
            $this->registry,
            $this->config,
            $this->loader,
            $this->session,
            $this->response,
            $this->modelOrder,
            $this->url,
            $this->modelPaypal,
            $this->language
        );
    }

    public function testGetConfig()
    {
        $this->config->expects($this->at(0))->method('get')->willReturn('account123');
        $this->config->expects($this->at(1))->method('get')->willReturn('secret123');
        $this->config->expects($this->at(2))->method('get')->willReturn('api-test.com');
        $this->config->expects($this->at(3))->method('get')->willReturn('user');
        $this->config->expects($this->at(4))->method('get')->willReturn('password');

        $this->controller = new ControllerExtensionPaymentWirecardPGPayPal(
            $this->registry,
            $this->config,
            $this->loader,
            $this->session,
            $this->response,
            $this->modelOrder,
            $this->url,
            $this->modelPaypal,
            $this->language
        );

        $expected = new \Wirecard\PaymentSdk\Config\Config('api-test.com', 'user', 'password');
        $expected->add(new \Wirecard\PaymentSdk\Config\PaymentMethodConfig(
            \Wirecard\PaymentSdk\Transaction\PayPalTransaction::NAME,
            'account123',
            'secret123'
        ));
        $expected->setShopInfo(self::SHOP, VERSION);
        $expected->setPluginInfo(self::PLUGIN, $this->pluginVersion);

        $actual = $this->controller->getConfig();

        $this->assertEquals($expected, $actual);
    }

    public function testGetModel()
    {
        $actual = $this->controller->getModel();

        $this->assertInstanceOf(get_class($this->modelPaypal), $actual);
    }

    public function testSuccessConfirm()
    {
        $this->controller->confirm();
        $json['response'] = [];
        $this->response->method('getOutput')->willReturn(json_encode($json));

        $expected = json_encode($json);

        $this->assertEquals($expected, $this->response->getOutput());
    }

    public function testSuccessConfirmWithDescriptor()
    {
        //Set descriptor true
        $this->config->expects($this->at(5))->method('get')->willReturn(1);
        $this->controller = new ControllerExtensionPaymentWirecardPGPayPal(
            $this->registry,
            $this->config,
            $this->loader,
            $this->session,
            $this->response,
            $this->modelOrder,
            $this->url,
            $this->modelPaypal,
            $this->language
        );

        $this->controller->confirm();
        $json['response'] = [];
        $this->response->method('getOutput')->willReturn(json_encode($json));

        $expected = json_encode($json);

        $this->assertEquals($expected, $this->response->getOutput());
    }

    public function testIndexActive()
    {
        $this->config->expects($this->at(0))->method('get')->willReturn(1);
        $this->loader->method('view')->willReturn('active');
        $this->controller = new ControllerExtensionPaymentWirecardPGPayPal(
            $this->registry,
            $this->config,
            $this->loader,
            $this->session,
            $this->response,
            $this->modelOrder,
            $this->url,
            $this->modelPaypal,
            $this->language
        );

        $actual = $this->controller->index();

        $this->assertNotNull($actual);
    }
}
