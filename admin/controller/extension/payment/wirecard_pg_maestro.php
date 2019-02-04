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
 * Class ControllerExtensionPaymentWirecardPGMaestro
 *
 * Maestro SecureCode payment transaction controller
 *
 * @since 1.1.0
 */
class ControllerExtensionPaymentWirecardPGMaestro extends \ControllerExtensionPaymentGateway {

	/**
	 * @var string
	 * @since 1.1.0
	 */
	protected $type = 'maestro';

	/**
	 * @var bool
	 * @since 1.1.0
	 */
	protected $has_payment_actions = true;

	/**
	 * Maestro SecureCode default configuration settings
	 *
	 * @var array
	 * @since 1.1.0
	 */
	protected $default = array (
		'status' => 0,
		'title' => 'Wirecard Maestro SecureCode',
		'merchant_account_id' => '4945f0ef-51e0-43af-972f-885405320842',
		'merchant_secret' => '822e87ea-dcc3-4d01-861c-e39f14a0ab6c',
		'base_url' => 'https://api-wdcee-test.wirecard.com',
		'http_password' => '4-41N4\lI0]783',
		'http_user' => 'plugin-test',
		'payment_action' => 'pay',
		'descriptor' => 0,
		'additional_info' => 1,
		'sort_order' => 5,
		'delete_cancel_order' => 0,
		'delete_failure_order' => 0,
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
			array('sort_order')
		);
	}
}
