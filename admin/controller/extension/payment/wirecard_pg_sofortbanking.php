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
 * Class ControllerExtensionPaymentWirecardPGSofortbanking
 *
 * Sofort. payment transaction controller
 *
 * @since 1.0.0
 */
class ControllerExtensionPaymentWirecardPGSofortbanking extends \ControllerExtensionPaymentGateway {

	/**
	 * @var string
	 * @since 1.0.0
	 */
	protected $type = 'sofortbanking';

	/**
	 * Sofort. default configuration settings
	 *
	 * @var array
	 * @since 1.0.0
	 */
	protected $default = array (
		'status' => 0,
		'title' => 'Wirecard Sofort.',
		'merchant_account_id' => '6c0e7efd-ee58-40f7-9bbd-5e7337a052cd',
		'merchant_secret' => 'dbc5a498-9a66-43b9-bf1d-a618dd399684',
		'base_url' => 'https://api-test.wirecard.com',
		'http_password' => '3!3013=D3fD8X7',
		'http_user' => '16390-testing',
		'payment_action' => 'pay',
		'descriptor' => 1,
		'additional_info' => 1,
		'delete_cancel_order' => 0,
		'delete_failure_order' => 0,
		'sort_order' => 9,
	);

	/**
	 * Set data fields or load config
	 *
	 * @return array
	 * @since 1.0.0
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
