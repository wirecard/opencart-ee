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
 * Class ControllerExtensionPaymentWirecardPGPayPal
 *
 * PayPal payment transaction controller
 *
 * @since 1.0.0
 */
class ControllerExtensionPaymentWirecardPGPayPal extends \ControllerExtensionPaymentGateway {

	/**
	 * @var string
	 * @since 1.0.0
	 */
	protected $type = 'paypal';

	/**
	 * @var bool
	 * @since 1.0.0
	 */
	protected $has_payment_actions = true;

	/**
	 * PayPal default configuration settings
	 *
	 * @var array
	 * @since 1.0.0
	 */
	protected $default = array (
		'status' => 0,
		'title' => 'Wirecard PayPal',
		'merchant_account_id' => '2a0e9351-24ed-4110-9a1b-fd0fee6bec26',
		'merchant_secret' => 'dbc5a498-9a66-43b9-bf1d-a618dd399684',
		'base_url' => 'https://api-test.wirecard.com',
		'http_password' => 'qD2wzQ_hrc!8',
		'http_user' => '70000-APITEST-AP',
		'payment_action' => 'pay',
		'shopping_basket' => '1',
		'descriptor' => '1',
		'additional_info' => '0',
		'sort_order' => '7',
		'delete_cancel_order' => '0',
		'delete_failure_order' => '0'
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
			'config_shopping_basket',
			'config_shopping_basket_desc',
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
		$this->config_fields = array_merge($this->config_fields, array(
			'shopping_basket',
			'sort_order',
		));

		return parent::getRequestData();
	}
}
