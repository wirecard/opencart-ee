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
 * Class ControllerExtensionPaymentWirecardPGAlipayCrossborder
 *
 * Alipay Cross-border payment transaction controller
 *
 * @since 1.1.0
 */
class ControllerExtensionPaymentWirecardPGAlipayCrossborder extends \ControllerExtensionPaymentGateway {

	/**
	 * @var string
	 * @since 1.1.0
	 */
	protected $type = 'alipay_crossborder';

	/**
	 * Alipay Cross-border default configuration settings
	 *
	 * @var array
	 * @since 1.1.0
	 */
	protected $default = array (
		'status' => 0,
		'title' => 'Wirecard Alipay Cross-border',
		'merchant_account_id' => '7ca48aa0-ab12-4560-ab4a-af1c477cce43',
		'merchant_secret' => 'dbc5a498-9a66-43b9-bf1d-a618dd399684',
		'base_url' => 'https://api-test.wirecard.com',
		'http_password' => 'qD2wzQ_hrc!8',
		'http_user' => '70000-APITEST-AP',
		'payment_action' => 'pay',
		'descriptor' => '0',
		'additional_info' => '1',
		'sort_order' => '2',
		'delete_cancel_order' => '0',
		'delete_failure_order' => '0',
		'logo_variant' => '0'
	);

	/**
	 * Set data fields or load config
	 *
	 * @return array
	 * @since 1.1.0
	 */
	protected function getRequestData() {
		$this->config_fields = array_merge($this->config_fields, array('sort_order', 'logo_variant'));

		return parent::getRequestData();
	}
}
