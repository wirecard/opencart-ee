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
 * Class ControllerExtensionPaymentWirecardPGPia
 *
 * Payment In Advance payment transaction controller
 *
 * @since 1.1.0
 */
class ControllerExtensionPaymentWirecardPGPia extends \ControllerExtensionPaymentGateway {

	/**
	 * @var string
	 * @since 1.1.0
	 */
	protected $type = 'pia';

	/**
	 * Payment On Invoice default configuration settings
	 *
	 * @var array
	 * @since 1.1.0
	 */
	protected $default = array (
		'status' => 0,
		'title' => 'Wirecard Payment In Advance',
		'merchant_account_id' => '105ab3e8-d16b-4fa0-9f1f-18dd9b390c94',
		'merchant_secret' => '2d96596b-9d10-4c98-ac47-4d56e22fd878',
		'base_url' => 'https://api-test.wirecard.com',
		'http_password' => 'qD2wzQ_hrc!8',
		'http_user' => '70000-APITEST-AP',
		'payment_action' => 'reserve',
		'descriptor' => 0,
		'additional_info' => 1,
		'delete_cancel_order' => 0,
		'delete_failure_order' => 0,
		'sort_order' => 6,
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
