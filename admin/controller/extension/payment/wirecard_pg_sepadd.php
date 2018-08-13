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
 * Class ControllerExtensionPaymentWirecardPGSEPADD
 *
 * SEPA Direct Debit payment transaction controller
 *
 * @since 1.1.0
 */
class ControllerExtensionPaymentWirecardPGSepaDD extends \ControllerExtensionPaymentGateway {

	/**
	 * @var string
	 * @since 1.1.0
	 */
	protected $type = 'sepadd';

	/**
	 * @var bool
	 * @since 1.1.0
	 */
	protected $has_payment_actions = true;

	/**
	 * SEPA Direct Debit default configuration settings
	 *
	 * @var array
	 * @since 1.1.0
	 */
	protected $default = array (
		'status' => 0,
		'title' => 'Wirecard SEPA Direct Debit',
		'merchant_account_id' => '933ad170-88f0-4c3d-a862-cff315ecfbc0',
		'merchant_secret' => '5caf2ed9-5f79-4e65-98cb-0b70d6f569aa',
		'base_url' => 'https://api-test.wirecard.com',
		'http_password' => '3!3013=D3fD8X7',
		'http_user' => '16390-testing',
		'payment_action' => 'pay',
		'creditor_id' => 'DE98ZZZ09999999999',
		'creditor_name' => '',
		'creditor_city' => '',
		'mandate_text' => '',
		'sort_order' => 8,
		'enable_bic' => 0,
		'descriptor' => 0,
		'additional_info' => 1,
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
	 * Load required blocks for configuration view.
	 *
	 * @param array $data
	 * @return array
	 * @since 1.1.0
	 */
	public function loadConfigBlocks($data) {
		$data = parent::loadConfigBlocks($data);
		$language_helper = new ControllerExtensionPaymentWirecardPGLanguageHelper($this->registry);

		$data['sepa_config'] = $this->load->view(
			'extension/payment/wirecard_pg/sepa_config',
			array_merge(
				$data,
				$language_helper->getConfigFields(['mandate_text'], $this->prefix, $this->type, $this->default)
			)
		);

		return $data;
	}

	/**
	 * Get mandatory fields that need to be set
	 *
	 * @return array
	 * @since 1.1.0
	 */
	public function getMandatoryFields() {
		$all_fields = parent::getMandatoryFields();

		return array_diff(
			$all_fields,
			array(
				'creditor_name',
				'creditor_city'
			)
		);
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
				'sort_order',
				'creditor_id',
				'creditor_name',
				'creditor_city',
				'enable_bic'
			)
		);
	}
}
