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
 * Class ControllerExtensionPaymentWirecardPGSEPACT
 *
 * SEPA Credit Transfer payment transaction controller
 *
 * @since 1.0.0
 */
class ControllerExtensionPaymentWirecardPGSepaCT extends \ControllerExtensionPaymentGateway {

	/**
	 * @var string
	 * @since 1.0.0
	 */
	protected $type = 'sepact';

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
	 * Basic index method
	 *
	 * @since 1.0.0
	 */
	public function index() {
		parent::index();
	}

	/**
	 * Set data fields or load config
	 *
	 * @return array
	 * @since 1.0.0
	 */
	protected function getRequestData() {
		$this->configFields = array_merge($this->configFields);

		return parent::getRequestData();
	}

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
