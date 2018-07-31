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
 * Class ControllerExtensionPaymentWirecardPGSEPACredit
 *
 * SEPA Credit Transfer payment transaction controller
 *
 * @since 1.0.0
 */
class ControllerExtensionPaymentWirecardPGSepaCredit extends \ControllerExtensionPaymentGateway {

	/**
	 * @var string
	 * @since 1.0.0
	 */
	protected $type = 'sepacredit';

	/**
	 * SEPA Credit Transfer default configuration settings
	 *
	 * @var array
	 * @since 1.0.0
	 */
	protected $default = array (
		'status' => 0,
		'title' => 'Wirecard SEPA Credit Transfer',
		'merchant_account_id' => '59a01668-693b-49f0-8a1f-f3c1ba025d45',
		'merchant_secret' => 'ecdf5990-0372-47cd-a55d-037dccfe9d25',
		'base_url' => 'https://api-test.wirecard.com',
		'http_password' => '3!3013=D3fD8X7',
		'http_user' => '16390-testing',
		'payment_action' => 'credit',
		'descriptor' => 0,
		'additional_info' => 0,
		'delete_cancel_order' => 0,
		'delete_failure_order' => 0,
	);

	/**
	 * Load the required config blocks for this payment method.
	 *
	 * @param array $data
	 * @return array
	 */
	public function loadConfigBlocks($data) {
		$data = parent::loadConfigBlocks($data);

		// The advanced configuration is not relevant for SEPA Credit Transfer
		unset($data['advanced_config']);

		return $data;
	}
}
