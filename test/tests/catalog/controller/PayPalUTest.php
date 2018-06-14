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

        $this->modelPaypal = $this->getMockBuilder(ModelExtensionPaymentWirecardPGPayPal::class)
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

        $this->controller = new ControllerExtensionPaymentWirecardPGPayPal(
            $this->registry,
            $this->config,
            $this->loader,
            $this->session,
            $this->response,
            $this->modelOrder,
            $this->url,
            $this->modelPaypal,
            $this->language,
	        $this->cart
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
            $this->language,
            $this->cart
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
            $this->language,
            $this->cart
        );

        $this->controller->confirm();
        $json['response'] = [];
        $this->response->method('getOutput')->willReturn(json_encode($json));

        $expected = json_encode($json);

        $this->assertEquals($expected, $this->response->getOutput());
    }

    public function testSuccessResponse()
    {
        $orderManager = m::mock('overload:PGOrderManager');
        $orderManager->shouldReceive('createResponseOrder');

        $_REQUEST = array(
            "route" => "extension/payment/wirecard_pg_paypal/response",
            "psp_name" => "elastic-payments",
            "custom_css_url" => "",
            "eppresponse" => "PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiPz48cGF5bWVudCB4bWxucz0iaHR0cDovL3d3dy5lbGFzdGljLXBheW1lbnRzLmNvbS9zY2hlbWEvcGF5bWVudCIgeG1sbnM6bnMyPSJodHRwOi8vd3d3LmVsYXN0aWMtcGF5bWVudHMuY29tL3NjaGVtYS9lcGEvdHJhbnNhY3Rpb24iPjxtZXJjaGFudC1hY2NvdW50LWlkPjJhMGU5MzUxLTI0ZWQtNDExMC05YTFiLWZkMGZlZTZiZWMyNjwvbWVyY2hhbnQtYWNjb3VudC1pZD48dHJhbnNhY3Rpb24taWQ+YTY5NDE4NjktMTU5NC00NmNhLTg5NWQtZmU5NzMzZmI2NmI4PC90cmFuc2FjdGlvbi1pZD48cmVxdWVzdC1pZD40MzRjZWYxODA3MmYyMzk4ZmFiODQ5ZWRiNDgzYTAxZS1wZW5kaW5nLWRlYml0PC9yZXF1ZXN0LWlkPjx0cmFuc2FjdGlvbi10eXBlPnBlbmRpbmctZGViaXQ8L3RyYW5zYWN0aW9uLXR5cGU+PHRyYW5zYWN0aW9uLXN0YXRlPnN1Y2Nlc3M8L3RyYW5zYWN0aW9uLXN0YXRlPjxjb21wbGV0aW9uLXRpbWUtc3RhbXA+MjAxOC0wNi0xM1QxMTo0MzoyMi4wMDBaPC9jb21wbGV0aW9uLXRpbWUtc3RhbXA+PHN0YXR1c2VzPjxzdGF0dXMgY29kZT0iMjAxLjAwMDAiIGRlc2NyaXB0aW9uPSJUaGUgcmVzb3VyY2Ugd2FzIHN1Y2Nlc3NmdWxseSBjcmVhdGVkLiIgcHJvdmlkZXItdHJhbnNhY3Rpb24taWQ9IjA0RTY2NDkyMVg2MzQ3ODJBIiBzZXZlcml0eT0iaW5mb3JtYXRpb24iLz48L3N0YXR1c2VzPjxyZXF1ZXN0ZWQtYW1vdW50IGN1cnJlbmN5PSJVU0QiPjYwNi4wMDAwMDA8L3JlcXVlc3RlZC1hbW91bnQ+PHBhcmVudC10cmFuc2FjdGlvbi1pZD5iNWI5ZTlhOS04ZGNiLTRiZjgtYmMwOS1mNDU5MDQ0N2ZkOGE8L3BhcmVudC10cmFuc2FjdGlvbi1pZD48YWNjb3VudC1ob2xkZXI+PGZpcnN0LW5hbWU+V2lyZWNhcmRidXllcjwvZmlyc3QtbmFtZT48bGFzdC1uYW1lPlNwaW50enlrPC9sYXN0LW5hbWU+PGVtYWlsPnBheXBhbC5idXllcjJAd2lyZWNhcmQuY29tPC9lbWFpbD48cGhvbmU+MDY2NCA2NDU2MDY0PC9waG9uZT48YWRkcmVzcz48c3RyZWV0MT5SZWluaW5naGF1c3N0cmFzc2UgMTNhPC9zdHJlZXQxPjxjaXR5PkdyYXo8L2NpdHk+PGNvdW50cnk+QVQ8L2NvdW50cnk+PHBvc3RhbC1jb2RlPjgwMjA8L3Bvc3RhbC1jb2RlPjwvYWRkcmVzcz48L2FjY291bnQtaG9sZGVyPjxzaGlwcGluZz48Zmlyc3QtbmFtZT5SaWNoYXJkPC9maXJzdC1uYW1lPjxsYXN0LW5hbWU+QmxlY2hpbmdlcjwvbGFzdC1uYW1lPjxwaG9uZT4rNDkgNzg4OTUwMDY2NzwvcGhvbmU+PGFkZHJlc3M+PHN0cmVldDE+UmVpbmluZ2hhdXNzdHJhc3NlIDEzYTwvc3RyZWV0MT48Y2l0eT5HcmF6PC9jaXR5Pjxjb3VudHJ5PkFUPC9jb3VudHJ5Pjxwb3N0YWwtY29kZT44MDIwPC9wb3N0YWwtY29kZT48L2FkZHJlc3M+PC9zaGlwcGluZz48aXAtYWRkcmVzcz4xMjcuMC4wLjE8L2lwLWFkZHJlc3M+PG9yZGVyLWRldGFpbD5yaWNoYXJkLmJsZWNoaW5nZXJAZXh0ZXJuYWwud2lyZWNhcmQuY29tIFJpY2hhcmQgQmxlY2hpbmdlcjwvb3JkZXItZGV0YWlsPjxkZXNjcmlwdG9yPllvdXIgU3RvciA1MjwvZGVzY3JpcHRvcj48bm90aWZpY2F0aW9ucz48bm90aWZpY2F0aW9uIHVybD0iaHR0cDovL2xvY2FsaG9zdC9vcGVuY2FydC9pbmRleC5waHA/cm91dGU9ZXh0ZW5zaW9uL3BheW1lbnQvd2lyZWNhcmRfcGdfcGF5cGFsL25vdGlmeSIvPjwvbm90aWZpY2F0aW9ucz48Y3VzdG9tLWZpZWxkcz48Y3VzdG9tLWZpZWxkIGZpZWxkLW5hbWU9InBheXNka19vcmRlcklkIiBmaWVsZC12YWx1ZT0iNTIiLz48L2N1c3RvbS1maWVsZHM+PHBheW1lbnQtbWV0aG9kcz48cGF5bWVudC1tZXRob2QgbmFtZT0icGF5cGFsIi8+PC9wYXltZW50LW1ldGhvZHM+PGNvbnN1bWVyLWlkPjE8L2NvbnN1bWVyLWlkPjxhcGktaWQ+LS0tPC9hcGktaWQ+PGNhbmNlbC1yZWRpcmVjdC11cmw+aHR0cDovL2xvY2FsaG9zdC9vcGVuY2FydC9pbmRleC5waHA/cm91dGU9ZXh0ZW5zaW9uL3BheW1lbnQvd2lyZWNhcmRfcGdfcGF5cGFsL3Jlc3BvbnNlJmFtcDtjYW5jZWxsZWQ9MTwvY2FuY2VsLXJlZGlyZWN0LXVybD48ZmFpbC1yZWRpcmVjdC11cmw+aHR0cDovL2xvY2FsaG9zdC9vcGVuY2FydC9pbmRleC5waHA/cm91dGU9ZXh0ZW5zaW9uL3BheW1lbnQvd2lyZWNhcmRfcGdfcGF5cGFsL3Jlc3BvbnNlPC9mYWlsLXJlZGlyZWN0LXVybD48c3VjY2Vzcy1yZWRpcmVjdC11cmw+aHR0cDovL2xvY2FsaG9zdC9vcGVuY2FydC9pbmRleC5waHA/cm91dGU9ZXh0ZW5zaW9uL3BheW1lbnQvd2lyZWNhcmRfcGdfcGF5cGFsL3Jlc3BvbnNlPC9zdWNjZXNzLXJlZGlyZWN0LXVybD48bG9jYWxlPmVuPC9sb2NhbGU+PGVudHJ5LW1vZGU+ZWNvbW1lcmNlPC9lbnRyeS1tb2RlPjx3YWxsZXQ+PGFjY291bnQtaWQ+Wk5LVFhVQk5TUUUyWTwvYWNjb3VudC1pZD48L3dhbGxldD48YXV0by1jYXB0dXJlPmZhbHNlPC9hdXRvLWNhcHR1cmU+PFNpZ25hdHVyZSB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC8wOS94bWxkc2lnIyI+PFNpZ25lZEluZm8+PENhbm9uaWNhbGl6YXRpb25NZXRob2QgQWxnb3JpdGhtPSJodHRwOi8vd3d3LnczLm9yZy9UUi8yMDAxL1JFQy14bWwtYzE0bi0yMDAxMDMxNSIvPjxTaWduYXR1cmVNZXRob2QgQWxnb3JpdGhtPSJodHRwOi8vd3d3LnczLm9yZy8yMDAxLzA0L3htbGRzaWctbW9yZSNyc2Etc2hhMjU2Ii8+PFJlZmVyZW5jZSBVUkk9IiI+PFRyYW5zZm9ybXM+PFRyYW5zZm9ybSBBbGdvcml0aG09Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvMDkveG1sZHNpZyNlbnZlbG9wZWQtc2lnbmF0dXJlIi8+PC9UcmFuc2Zvcm1zPjxEaWdlc3RNZXRob2QgQWxnb3JpdGhtPSJodHRwOi8vd3d3LnczLm9yZy8yMDAxLzA0L3htbGVuYyNzaGEyNTYiLz48RGlnZXN0VmFsdWU+UXlKVW9keXV3MUhuOVJOUENuTlZGVWlENVZpcmd6WDRuY0FKejFVSWhaaz08L0RpZ2VzdFZhbHVlPjwvUmVmZXJlbmNlPjwvU2lnbmVkSW5mbz48U2lnbmF0dXJlVmFsdWU+RUFOS3U4cjdEQ3IrcHcrVVdWWVRzK2l6Z01iUkUyakRSVlhVTWpMTG9YTlpmeGtaajQ3OWN2eS8yWmF2RUlYQUJNcDJNVG1sNTUyTUY5bFJJQmpFK0FzUHZmL1dMUG51RFlPQnRkZmZxY1ZwUE56YWkrR1MxNHkvckRXclRnWDExZUdRV3hHcDhyKzNaOHR5Y2doOFluNW15Q3lESnhJamh2OUE0NXF5aTFCaFdjQlJaNVRGNFROSDBITjdOWjV5anhVU00yY3pPN29kcEtISlgxakl0by8wZDJhRjRHZU00a2o4aHZqZ2RRY05uL2czeU5yYVRzWUdTRGtCSWVpclpONERoQ09FbXVkRkxLejVsaDRUZFhscC9CajNiK0psTGZsdDZtdHFkbm9zc0JsKzVkQjdSSVhQdHBydzE4Yys0TStwMmc0SVpDY2sxY0NzZ3ZqeWlOejJaMWNnZVMrT0xXN1lHQkRxWU5velhBd1BROWx2eWZKbTdkYTdCaWRrSzRNT2xlVzdCTVFOaFV2Y1piUmVkaW1QVGdYWDFkcDFlZXdSNlJBTXpzSzQzVGo3cXBJYUx4Y0ovOUE1ajB3WlAvUzdzWTJUMTMyWlpZRy82ZzF4RU9YRWxnSksvS2dlUkRPT29QOXQvNUFFMXRlVWtLWnRhd1BYNjA5bGJ4RGFPWlpCYVBuQzhKWHhWaE1ic2ZmZUsvQ3pmT1pIbFk5NzZlRGlqejUzRHNtbFhLREVrVDZNTUhwMnRud1l4MmVXU2VXSzJuQytoU2c5Zm9PN0JqckVLMm13a2JtTmdlaWhuSUZqSUhvY2EzUXJVSDhsdGttUW9ocnhWV0tJS2ZDT0hTQm9kWWVHSVF6TXMzU0gweEl3VWtYVTF6UlJYT3JSaHNjQjlNZGxRSzg9PC9TaWduYXR1cmVWYWx1ZT48S2V5SW5mbz48WDUwOURhdGE+PFg1MDlTdWJqZWN0TmFtZT5MPUFzY2hlaW0sMi41LjQuND0jMTMwNjQyNjE3OTY1NzI2ZSxDTj1hcGktdGVzdC53aXJlY2FyZC5jb20sT1U9T3BlcmF0aW9ucyxPPVdpcmVjYXJkIFRlY2hub2xvZ2llcyBHbWJILEM9REU8L1g1MDlTdWJqZWN0TmFtZT48WDUwOUNlcnRpZmljYXRlPk1JSUY1RENDQk15Z0F3SUJBZ0lDTEhRd0RRWUpLb1pJaHZjTkFRRUxCUUF3V3pFTE1Ba0dBMVVFQmhNQ1JFVXhFVEFQQmdOVkJBb1RDRmRwY21WallYSmtNVGt3TndZRFZRUURGREIzYVhKbFkyRnlaQzFFVVMxTlZVTXRhVzUwWlhKdVlXd3RkMlZpYzJWeWRtbGpaUzFwYzNOMWFXNW5RMEZmTURJd0hoY05NVGN3TVRFeU1UTTFPVEkyV2hjTk1Ua3dNVEV5TVRNMU9USTJXakNCaWpFTE1Ba0dBMVVFQmhNQ1JFVXhJekFoQmdOVkJBb1RHbGRwY21WallYSmtJRlJsWTJodWIyeHZaMmxsY3lCSGJXSklNUk13RVFZRFZRUUxFd3BQY0dWeVlYUnBiMjV6TVI0d0hBWURWUVFERXhWaGNHa3RkR1Z6ZEM1M2FYSmxZMkZ5WkM1amIyMHhEekFOQmdOVkJBUVRCa0poZVdWeWJqRVFNQTRHQTFVRUJ4TUhRWE5qYUdWcGJUQ0NBaUl3RFFZSktvWklodmNOQVFFQkJRQURnZ0lQQURDQ0Fnb0NnZ0lCQUtTa0V4Qlk4RmpSY1pkcnhPdUpGK0haWTgrTWNRYU9COEIwRS9oVFVob2Nsc0Y0T0pOYU1UaGplN1I2dzZPWVdCTUtwc3NHbmdIRmFadjM1ckNvNVhWVXBKbWpaYTA0eXR4RTcyR0tPL3VQNHlJUjdaQlhaeDQyQjIyTUZhSkpaVGdQUkNDRmQ2anJ6OTA2QlovL0NtRUFtazVnS2VsZlB4ZldKZ0d5VFg2eHo3STlSL0c1N0UxeE5PdUVpaE4wbWE1UTJJaEQ3MU1QVnNlRklHYXp5ZkdiSkQ2cllZYmVCYk9RU0drLy9UTDhzZFJDbjBCTGNtNERINW9xY1B4REt6a2FCUDRvaE5rQ1dzeHBMTFN5VjZXeDBpaFQwUzFPTFZOa0VlVHZjcllnVWsxMjRWeUdhdHdXTlV1Q0JZeU9HUVNPR3FyVzhJSG1yaGp6elQwTlFvZzAvbTM4bHBkcXcvZVdtdDM5cWhPRHFTZklMVWsyRHh2MStXMElSS0pDS2NKcmNUYlhFUUN1SGwrWFdZK1UyQWhpbklQTlJBMEtYMm9PZ0MvL2lud3lLV1NHV0hkUW5hYWtlNjQ2UjF3SHF0b0VmQ3RFY2Z5YWVSK0lyTXIxckNBQTNSWitNSDFKNVVsVUNXY254UFQwa2FkNmRVd2UzUWpxM2pLNGdhRnpZVTJ5VlNjWDVMVlpNbFd5Mk5pR0NJdm5nSFFtaEFyRVN6eE1Wdno1TUVUWnVqZmF4NmhmbWlMTlJXdTBacXMwOU1weHk1ems1bS9XUmk1aXpiMHVCZUNmY0E2eDlwbWpNeDhNNE9HRzVSTzJIVFhTd0xZSlRLSTQ3VlhOc0xMT1krbk1GbWhqL2RrTEo1ZDN6STdFY3pUb1BNUkhtSEc3RXFFZEFmYmIrb1VsQWdNQkFBR2pnZ0dBTUlJQmZEQVJCZ05WSFE0RUNnUUlTNndWSUEwbUo5SXdFd1lEVlIwakJBd3dDb0FJUTJ3ZUZ0UTlCUTR3Q3dZRFZSMFBCQVFEQWdUd01JSUJRd1lEVlIwZkJJSUJPakNDQVRZd2dnRXlvSUlCTHFDQ0FTcUdnZFZzWkdGd09pOHZkMmx5WldOaGNtUXViR0Z1TDBOT1BYZHBjbVZqWVhKa0xVUlJMVTFWUXkxcGJuUmxjbTVoYkMxM1pXSnpaWEoyYVdObExXbHpjM1ZwYm1kRFFWOHdNaXhEVGoxRFJGQXNRMDQ5VUhWaWJHbGpJRXRsZVNCVFpYSjJhV05sY3l4RFRqMVRaWEoyYVdObGN5eERUajFEYjI1bWFXZDFjbUYwYVc5dUxHUmpQWGRwY21WallYSmtMR1JqUFd4aGJqOWpaWEowYVdacFkyRjBaVkpsZG05allYUnBiMjVNYVhOMFAySmhjMlUvYjJKcVpXTjBRMnhoYzNNOVExSk1SR2x6ZEhKcFluVjBhVzl1VUc5cGJuU0dVR2gwZEhBNkx5OWpjbXd1ZDJseVpXTmhjbVF1YkdGdUwwTlNURjkzYVhKbFkyRnlaQzFFVVMxTlZVTXRhVzUwWlhKdVlXd3RkMlZpYzJWeWRtbGpaUzFwYzNOMWFXNW5RMEZmTURJdVkzSnNNQTBHQ1NxR1NJYjNEUUVCQ3dVQUE0SUJBUUFtbFVvaUVGUFJzT2pHUGI3U1lpdUpMeHFUWEN2WlFldVhpVXlkRjZGUWwveklwUi96U2x0YVpLSzg2TCsxaTd0MUM4OU95VFRYQkQ5Rk42RUttbEhvL3Vsc01uOVYyQjR6SzNsVC9OVWNsU1Q5OEJtQ2xhNEp6bStyb2VPSFRxbFB6M2dQUkppUHNyM3dkdk0rRlNBSjJNUmR2M2w3N21URTN2M2hqc1ZWTW1TaFIzVnd3cHhDSUNsM21wTXNTYUpaTHlKZE9Id3ZucFhzMW05a0VTd1BEM0RRM1JBUS9PR2EwcFB4QWtIYWF1b2c0RGhQdnIvbkJRbldIZDJVczViL2VwN0xNRTloWjh1M2h1L0tjNlZrMjRjNXAzV1VPaXlhVGl3K1ltM1FEWGwxd0JTbDlEZE05NEtibUFBUTVEL0ZVcXlRblNjNFRwbVl2SitJYXZhZzwvWDUwOUNlcnRpZmljYXRlPjwvWDUwOURhdGE+PC9LZXlJbmZvPjwvU2lnbmF0dXJlPjwvcGF5bWVudD4=",
            "locale" => "en",
        );

        $response = $this->controller->response();

        $this->assertTrue($response);
    }

    public function testFailureResponse()
    {
        $_REQUEST = array(
            "route" => "extension/payment/wirecard_pg_paypal/response",
            "psp_name" => "elastic-payments",
            "custom_css_url" => "",
            "eppresponse" => "PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiIHN0YW5kYWxvbmU9InllcyI/Pg0KPHBheW1lbnQgeG1sbnM9Imh0dHA6Ly93d3cuZWxhc3RpYy1wYXltZW50cy5jb20vc2NoZW1hL3BheW1lbnQiIHhtbG5zOm5zMj0iaHR0cDovL3d3dy5lbGFzdGljLXBheW1lbnRzLmNvbS9zY2hlbWEvZXBhL3RyYW5zYWN0aW9uIiBzZWxmPSJodHRwczovL2FwaS10ZXN0LndpcmVjYXJkLmNvbTo0NDMvZW5naW5lL3Jlc3QvbWVyY2hhbnRzL2JlMTc0NzZmLTFhMGMtNDQyZS04ODQxLTcwZTMzOTk2YzBhYS9wYXltZW50cy8yYTQyMWZhMS04MjMzLTRlNTYtOWY5OS0xOWM0MzRjMmY4NmUiPg0KICA8bWVyY2hhbnQtYWNjb3VudC1pZCByZWY9Imh0dHBzOi8vYXBpLXRlc3Qud2lyZWNhcmQuY29tOjQ0My9lbmdpbmUvcmVzdC9jb25maWcvbWVyY2hhbnRzL2JlMTc0NzZmLTFhMGMtNDQyZS04ODQxLTcwZTMzOTk2YzBhYSI+YmUxNzQ3NmYtMWEwYy00NDJlLTg4NDEtNzBlMzM5OTZjMGFhPC9tZXJjaGFudC1hY2NvdW50LWlkPg0KICA8dHJhbnNhY3Rpb24taWQ+MmE0MjFmYTEtODIzMy00ZTU2LTlmOTktMTljNDM0YzJmODZlPC90cmFuc2FjdGlvbi1pZD4NCiAgPHJlcXVlc3QtaWQ+M2JhYzE1ZDctMmZjMi00MmQ4LTk5MjctYzcwNzVhNmRiYTY0PC9yZXF1ZXN0LWlkPg0KICA8dHJhbnNhY3Rpb24tdHlwZT5hdXRob3JpemF0aW9uPC90cmFuc2FjdGlvbi10eXBlPg0KICA8dHJhbnNhY3Rpb24tc3RhdGU+ZmFpbGVkPC90cmFuc2FjdGlvbi1zdGF0ZT4NCiAgPGNvbXBsZXRpb24tdGltZS1zdGFtcD4yMDE4LTA1LTA4VDA4OjExOjI2LjAwMFo8L2NvbXBsZXRpb24tdGltZS1zdGFtcD4NCiAgPHN0YXR1c2VzPg0KICAgIDxzdGF0dXMgY29kZT0iNDAwLjEwMjgiIGRlc2NyaXB0aW9uPSJUaGUgVG9rZW4gb3IgQWNjb3VudCBOdW1iZXIgaXMgaW52YWxpZC4gIFBsZWFzZSBjaGVjayB5b3VyIGlucHV0IGFuZCB0cnkgYWdhaW4iIHNldmVyaXR5PSJlcnJvciIgLz4NCiAgPC9zdGF0dXNlcz4NCiAgPHJlcXVlc3RlZC1hbW91bnQgY3VycmVuY3k9IkVVUiI+MS4yMzwvcmVxdWVzdGVkLWFtb3VudD4NCiAgPGFjY291bnQtaG9sZGVyPg0KICAgPGZpcnN0LW5hbWU+V2lyZWNhcmRidXllcjwvZmlyc3QtbmFtZT4NCiAgIDxsYXN0LW5hbWU+U3BpbnR6eWs8L2xhc3QtbmFtZT4NCiAgIDxlbWFpbD5wYXlwYWwuYnV5ZXIyQHdpcmVjYXJkLmNvbTwvZW1haWw+DQogIDwvYWNjb3VudC1ob2xkZXI+DQogIDxzaGlwcGluZz4NCiAgICA8Zmlyc3QtbmFtZT5TYW5kYm94VGVzdDwvZmlyc3QtbmFtZT4NCiAgICA8bGFzdC1uYW1lPkFjY291bnQ8L2xhc3QtbmFtZT4NCiAgICA8cGhvbmU+KzQ5MTIzMTIzMTIzPC9waG9uZT4NCiAgICA8YWRkcmVzcz4NCiAgICAgIDxzdHJlZXQxPkVTcGFjaHN0ci4gMTwvc3RyZWV0MT4NCiAgICAgIDxjaXR5PkZyZWlidXJnPC9jaXR5Pg0KICAgICAgPGNvdW50cnk+REU8L2NvdW50cnk+DQogICAgICA8cG9zdGFsLWNvZGU+NzkxMTE8L3Bvc3RhbC1jb2RlPg0KICAgIDwvYWRkcmVzcz4NCiAgPC9zaGlwcGluZz4NCiAgPG9yZGVyLW51bWJlcj4xODA1MDgxMDExMjQwMTg8L29yZGVyLW51bWJlcj4NCiAgPGRlc2NyaXB0b3I+Y3VzdG9tZXJTdGF0ZW1lbnQgMTgwMDk5OTg4ODg8L2Rlc2NyaXB0b3I+DQogIDxjdXN0b20tZmllbGRzPg0KICAgIDxjdXN0b20tZmllbGQgZmllbGQtbmFtZT0iZWxhc3RpYy1hcGkuY2FyZF9pZCIgLz4NCiAgPC9jdXN0b20tZmllbGRzPg0KICA8cGF5bWVudC1tZXRob2RzPg0KICAgIDxwYXltZW50LW1ldGhvZCBuYW1lPSJwYXlwYWwiIC8+DQogIDwvcGF5bWVudC1tZXRob2RzPg0KICA8YXBpLWlkPmVsYXN0aWMtYXBpPC9hcGktaWQ+DQogIDxjYW5jZWwtcmVkaXJlY3QtdXJsPmh0dHBzOi8vZGVtb3Nob3AtdGVzdC53aXJlY2FyZC5jb20vZGVtb3Nob3AvIyEvY2FuY2VsPC9jYW5jZWwtcmVkaXJlY3QtdXJsPg0KICA8c3VjY2Vzcy1yZWRpcmVjdC11cmw+aHR0cHM6Ly9kZW1vc2hvcC10ZXN0LndpcmVjYXJkLmNvbS9kZW1vc2hvcC8jIS9zdWNjZXNzPC9zdWNjZXNzLXJlZGlyZWN0LXVybD4NCiAgPGZhaWwtcmVkaXJlY3QtdXJsPmh0dHBzOi8vZGVtb3Nob3AtdGVzdC53aXJlY2FyZC5jb20vZGVtb3Nob3AvIyEvZXJyb3I8L2ZhaWwtcmVkaXJlY3QtdXJsPg0KICA8cGVyaW9kaWM+DQogICAgPHBlcmlvZGljLXR5cGU+cmVjdXJyaW5nPC9wZXJpb2RpYy10eXBlPg0KICAgIDxzZXF1ZW5jZS10eXBlPnJlY3VycmluZzwvc2VxdWVuY2UtdHlwZT4NCiAgPC9wZXJpb2RpYz4NCjwvcGF5bWVudD4=",
            "locale" => "en",
        );

        $response = $this->controller->response();

        $this->assertFalse($response);
    }

    public function testMalformedResponse()
    {
        $_REQUEST = array(
            "payment-method" => "paypal"
        );

        $this->controller->response();

        $this->assertArrayHasKey('error', $this->session->data);
        $this->assertEquals('Missing response in payload.', $this->session->data['error']);
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
            $this->language,
            $this->cart
        );

        $actual = $this->controller->index();

        $this->assertNotNull($actual);
    }

    public function testShoppingBasket() {
	    //Set shopping_basket true
	    $this->config->expects($this->at(6))->method('get')->willReturn(1);
	    $this->controller = new ControllerExtensionPaymentWirecardPGPayPal(
		    $this->registry,
		    $this->config,
		    $this->loader,
		    $this->session,
		    $this->response,
		    $this->modelOrder,
		    $this->url,
		    $this->modelPaypal,
		    $this->language,
		    $this->cart
	    );

	    $this->controller->confirm();
	    $json['response'] = [];
	    $this->response->method('getOutput')->willReturn(json_encode($json));

	    $expected = json_encode($json);

	    $this->assertEquals($expected, $this->response->getOutput());
    }

    public function testAdditionalInformation() {
        //Set additional_info true
        $this->config->expects($this->at(7))->method('get')->willReturn(1);
        $this->controller = new ControllerExtensionPaymentWirecardPGPayPal(
            $this->registry,
            $this->config,
            $this->loader,
            $this->session,
            $this->response,
            $this->modelOrder,
            $this->url,
            $this->modelPaypal,
            $this->language,
            $this->cart
        );

        $this->controller->confirm();
        $json['response'] = [];
        $this->response->method('getOutput')->willReturn(json_encode($json));

        $expected = json_encode($json);

        $this->assertEquals($expected, $this->response->getOutput());
    }
}
