<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/opencart-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/opencart-ee/blob/master/LICENSE
 */

require_once(dirname(__FILE__) . '/wirecard_pg/gateway.php');

/**
 * Class ControllerExtensionPaymentWirecardPGUPI
 *
 * Unionpay International payment transaction controller
 *
 * @since 1.1.0
 */
class ControllerExtensionPaymentWirecardPGUPI extends \ControllerExtensionPaymentGateway {

	/**
	 * @var string
	 * @since 1.1.0
	 */
	protected $type = 'upi';

	/**
	 * @var bool
	 * @since 1.1.0
	 */
	protected $has_payment_actions = true;

	/**
	 * UnionPay International default configuration settings
	 *
	 * @var array
	 * @since 1.1.0
	 */
	protected $default = array(
		'status' => 0,
		'title' => 'Wirecard UnionPay International',
		'merchant_account_id' => 'c6e9331c-5c1f-4fc6-8a08-ef65ce09ddb0',
		'merchant_secret' => '16d85b73-79e2-4c33-932a-7da99fb04a9c',
		'base_url' => 'https://api-test.wirecard.com',
		'http_password' => '8mhwavKVb91T',
		'http_user' => '70000-APILUHN-CARD',
		'payment_action' => 'pay',
		'descriptor' => 0,
		'additional_info' => 1,
		'delete_cancel_order' => 0,
		'delete_failure_order' => 0,
		'sort_order' => 10,
	);

	/**
	 * Set data fields or load config
	 *
	 * @return array
	 * @since 1.1.0
	 */
	protected function getRequestData() {
		$this->config_fields = array_merge($this->config_fields, array('sort_order'));

		return parent::getRequestData();
	}
}
