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
 * Class ControllerExtensionPaymentWirecardPGIdeal
 *
 * iDEAL payment transaction controller
 *
 * @since 1.0.0
 */
class ControllerExtensionPaymentWirecardPGIdeal extends \ControllerExtensionPaymentGateway {

	/**
	 * @var string
	 * @since 1.0.0
	 */
	protected $type = 'ideal';

	/**
	 * iDEAL default configuration settings
	 *
	 * @var array
	 * @since 1.0.0
	 */
	protected $default = array (
		'status' => 0,
		'title' => 'Wirecard iDEAL',
		'merchant_account_id' => '4aeccf39-0d47-47f6-a399-c05c1f2fc819',
		'merchant_secret' => 'dbc5a498-9a66-43b9-bf1d-a618dd399684',
		'base_url' => 'https://api-test.wirecard.com',
		'http_password' => '3!3013=D3fD8X7',
		'http_user' => '16390-testing',
		'payment_action' => 'pay',
		'descriptor' => 0,
		'additional_info' => 1,
		'delete_cancel_order' => 0,
		'delete_failure_order' => 0,
		'sort_order' => '4'
	);

	/**
	 * Set data fields or load config
	 *
	 * @return array
	 * @since 1.0.0
	 */
	protected function getRequestData() {
		$this->config_fields = array_merge($this->config_fields, array('sort_order'));

		return parent::getRequestData();
	}
}
