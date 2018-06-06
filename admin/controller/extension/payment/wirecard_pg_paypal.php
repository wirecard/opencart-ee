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

require_once(dirname(__FILE__) . '/wirecard_pg/gateway.php');

/**
 * Class ControllerExtensionPaymentWirecardPGPayPal
 *
 * PayPal payment transaction controller
 *
 * @since 1.0.0
 */
class ControllerExtensionPaymentWirecardPGPayPal extends \ControllerExtensionPaymentGateway {

	/**
	 * @var string
	 * @since 1.0.0
	 */
	protected $type = 'paypal';

	/**
	 * PayPal default configuration settings
	 *
	 * @var array
	 * @since 1.0.0
	 */
	protected $default = array(
		'merchant_account_id' => '2a0e9351-24ed-4110-9a1b-fd0fee6bec26',
		'merchant_secret' => 'dbc5a498-9a66-43b9-bf1d-a618dd399684',
		'base_url' => 'https://api-test.wirecard.com',
		'http_password' => 'qD2wzQ_hrc!8',
		'http_user' => '70000-APITEST-AP',
		'descriptor' => '1',
        'additional_info' => '0',
        'session_string' => '1random-session-string',
	);

	/**
	 * Basic index method
	 *
	 * @since 1.0.0
	 */
	public function index() {
		parent::index();
	}
}
