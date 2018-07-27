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
 * Class ControllerExtensionPaymentWirecardPGMasterpass
 *
 * Masterpass payment transaction controller
 *
 * @since 1.1.0
 */
class ControllerExtensionPaymentWirecardPGMasterpass extends \ControllerExtensionPaymentGateway {

	/**
	 * @var string
	 * @since 1.1.0
	 */
	protected $type = 'masterpass';

	/**
	 * @var bool
	 * @since 1.1.0
	 */
	protected $has_payment_actions = true;

	/**
	 * Masterpass default configuration settings
	 *
	 * @var array
	 * @since 1.1.0
	 */
	protected $default = array (
		'status' => 0,
		'title' => 'Wirecard Masterpass',
		'merchant_account_id' => '8bc8ed6d-81a8-43be-bd7b-75b008f89fa6',
		'merchant_secret' => '2d96596b-9d10-4c98-ac47-4d56e22fd878',
		'base_url' => 'https://api-test.wirecard.com',
		'http_password' => 'qD2wzQ_hrc!8',
		'http_user' => '70000-APITEST-AP',
		'payment_action' => 'pay',
		'descriptor' => 0,
		'additional_info' => 1,
		'sort_order' => 5,
		'delete_cancel_order' => 0,
		'delete_failure_order' => 0
	);

	/**
	 * Set data fields or load config
	 *
	 * @return array
	 * @since 1.1.0
	 */
	protected function getRequestData() {
		$this->config_fields = $this->getPaymentConfigFields();

		return parent::getRequestData();
	}

	/**
	 * Return payment config fields
	 *
	 * @return array
	 * @since 1.1.0
	 */
	public function getPaymentConfigFields() {
		return array_merge(
			$this->config_fields,
			array(
				'sort_order'
			)
		);
	}
}
