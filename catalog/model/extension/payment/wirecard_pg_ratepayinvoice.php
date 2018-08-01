<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/opencart-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/opencart-ee/blob/master/LICENSE
 */

include_once(DIR_SYSTEM . 'library/autoload.php');
require_once(dirname( __FILE__ ) . '/wirecard_pg/gateway.php');

/**
 * Class ModelExtensionPaymentWirecardPGRatepayInvoice
 *
 * Guaranteed Invoice Transaction model
 *
 * @since 1.1.0
 */
class ModelExtensionPaymentWirecardPGRatepayInvoice extends ModelExtensionPaymentGateway {

	/**
	 * @var string
	 * @since 1.1.0
	 */
	protected $type = 'ratepayinvoice';

	/**
	 * Basic getMethod method
	 *
	 * @since 1.1.0
	 */
	public function getMethod($address, $total) {
		$prefix = $this->prefix . $this->type;
		$additional_info = new AdditionalInformationHelper($this->registry, $prefix, $this->config);
		$shipping_address = $this->session->data['shipping_address'];
		$payment_address = $this->session->data['payment_address'];
		$allowed_shipping = $this->config->get($prefix . '_shipping_countries');
		$allowed_billing = $this->config->get($prefix . '_billing_countries');
		$allowed_currencies = $this->config->get($prefix . '_allowed_currencies');

		if ($this->config->get($prefix . '_billing_shipping')) {
			$fields = array(
				'firstname',
				'lastname',
				'company',
				'address_1',
				'address_2',
				'postcode',
				'city',
				'zone',
				'country'
			);
			foreach ($fields as $field) {
				if ($payment_address[$field] != $shipping_address[$field]) {
					return false;
				}
			}
		}

		if (!in_array($shipping_address['iso_code_2'], $allowed_shipping)) {
			return false;
		}
		if (!in_array($payment_address['iso_code_2'], $allowed_billing)) {
			return false;
		}
		if (!in_array($this->session->data['currency'], $allowed_currencies)) {
			return false;
		}

		$amount = $additional_info->convert($total, $additional_info->getCurrency($this->session->data['currency'], $this->type));

		if ($amount < $this->config->get($prefix . '_basket_min') || $amount > $this->config->get($prefix . '_basket_max')) {
			return false;
		}

		return parent::getMethod($address, $total);
	}
}
