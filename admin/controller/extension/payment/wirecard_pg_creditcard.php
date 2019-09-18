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
 * Class ControllerExtensionPaymentWirecardPGCreditCard
 *
 * CreditCard payment transaction controller
 *
 * @since 1.0.0
 */
class ControllerExtensionPaymentWirecardPGCreditCard extends \ControllerExtensionPaymentGateway {

	/**
	 * @var string
	 * @since 1.0.0
	 */
	protected $type = 'creditcard';

	/**
	 * @var bool
	 * @since 1.0.0
	 */
	protected $has_payment_actions = true;

	/**
	 * Credit Card default configuration settings
	 *
	 * @var array
	 * @since 1.0.0
	 */
	protected $default = array (
		'status' => 0,
		'title' => 'Wirecard Credit Card',
		'merchant_account_id' => '53f2895a-e4de-4e82-a813-0d87a10e55e6',
		'merchant_secret' => 'dbc5a498-9a66-43b9-bf1d-a618dd399684',
		'three_d_merchant_account_id' => '508b8896-b37d-4614-845c-26bf8bf2c948',
		'three_d_merchant_secret' => 'dbc5a498-9a66-43b9-bf1d-a618dd399684',
		'ssl_max_limit' => 300,
		'three_d_min_limit' => 100,
		'base_url' => 'https://api-test.wirecard.com',
		'http_password' => 'qD2wzQ_hrc!8',
		'http_user' => '70000-APITEST-AP',
		'payment_action' => 'pay',
		'descriptor' => 0,
		'additional_info' => 1,
		'sort_order' => 1,
		'delete_cancel_order' => 0,
		'delete_failure_order' => 0,
		'vault' => 0,
		'allow_changed_shipping' => 0,
		'challenge_indicator' => '01',
	);

	/**
	 * Get text for config fields
	 *
	 * @param array $fields
	 * @return mixed
	 * @since 1.0.0
	 */
	protected function getConfigText($fields = []) {
		$fields = array(
			'config_merchant_account_id_cc_desc',
			'config_merchant_secret_cc_desc',
			'config_three_d_merchant_account_id',
			'config_three_d_merchant_account_id_desc',
			'config_three_d_merchant_secret',
			'config_three_d_merchant_secret_desc',
			'config_ssl_max_limit',
			'config_three_d_min_limit',
			'config_limit_desc',
			'config_vault',
			'config_vault_desc',
			'config_allow_changed_shipping',
			'config_allow_changed_shipping_desc',
			'config_challenge_indicator',
			'config_challenge_indicator_desc',
		);
		return parent::getConfigText($fields);
	}

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
	 * Load required blocks for configuration view.
	 *
	 * @param array $data
	 * @return array
	 */
	public function loadConfigBlocks($data) {
		$data = parent::loadConfigBlocks($data);

		$data['three_d_config'] = $this->load->view('extension/payment/wirecard_pg/three_d_config', $data);
		$data['vault_config']   = $this->load->view('extension/payment/wirecard_pg/vault_config', $data);
		$data['available_challenge_indicators'] = [
			'config_challenge_no_preference' => Wirecard\PaymentSdk\Constant\ChallengeInd::NO_PREFERENCE,
			'config_challenge_no_challenge' => Wirecard\PaymentSdk\Constant\ChallengeInd::NO_CHALLENGE,
			'config_challenge_challenge_threed' => Wirecard\PaymentSdk\Constant\ChallengeInd::CHALLENGE_THREED,
		];
		$data['challenge_indicator_config'] = $this->load->view(
			'extension/payment/wirecard_pg/challenge_indicator_config',
			$data
		);

		return $data;
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
				'three_d_merchant_account_id',
				'three_d_merchant_secret',
				'ssl_max_limit',
				'three_d_min_limit',
				'sort_order',
				'vault',
				'allow_changed_shipping',
				'challenge_indicator',
			)
		);
	}
}
