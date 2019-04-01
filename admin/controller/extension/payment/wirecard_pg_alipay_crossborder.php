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
		'merchant_account_id' => '47cd4edf-b13c-4298-9344-53119ab8b9df',
		'merchant_secret' => '94fe4f40-16c5-4019-9c6c-bc33ec858b1d',
		'base_url' => 'https://api-test.wirecard.com',
		'http_user' => '16390-testing',
		'http_password' => '3!3013=D3fD8X7',
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
		$this->config_fields = array_merge($this->config_fields, array('sort_order'));

		return parent::getRequestData();
	}
}
