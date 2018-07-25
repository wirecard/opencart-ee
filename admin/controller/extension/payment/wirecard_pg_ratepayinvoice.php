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
 * Class ControllerExtensionPaymentWirecardPGRatepayInvoice
 *
 * Guaranteed Invoice payment transaction controller
 *
 * @since 1.1.0
 */
class ControllerExtensionPaymentWirecardPGRatepayInvoice extends \ControllerExtensionPaymentGateway {

	/**
	 * @var string
	 * @since 1.1.0
	 */
	protected $type = 'ratepayinvoice';

	/**
	 * Guaranteed Invoice default configuration settings
	 *
	 * @var array
	 * @since 1.1.0
	 */
	protected $default = array (
		'status' => 0,
		'title' => 'Wirecard Guaranteed Invoice by Wirecard / Ratepay',
		'merchant_account_id' => '73ce088c-b195-4977-8ea8-0be32cca9c2e',
		'merchant_secret' => 'd92724cf-5508-44fd-ad67-695e149212d5',
		'base_url' => 'https://api-test.wirecard.com',
		'http_password' => 'qD2wzQ_hrc!8',
		'http_user' => '70000-APITEST-AP',
		'payment_action' => 'reserve',
		'basket_min' => 20,
		'basket_max' => 3500,
		'allowed_currencies' => array('EUR'),
		'shipping_countries' => array('AT', 'DE'),
		'billing_countries' => array('AT', 'DE'),
		'billing_shipping' => 1,
		'descriptor' => 0,
		'additional_info' => 1,
		'sort_order' => 3,
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
		$this->config_fields = array_merge(
			$this->config_fields,
			array(
				'sort_order',
				'basket_min',
				'basket_max',
				'allowed_currencies',
				'shipping_countries',
				'billing_countries',
				'billing_shipping'
			)
		);

		return parent::getRequestData();
	}
}
