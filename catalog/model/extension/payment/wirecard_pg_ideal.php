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
require_once(dirname( __FILE__ ) . '/wirecard_pg/gateway.php');

use Wirecard\PaymentSdk\Entity\IdealBic;

/**
 * Class ModelExtensionPaymentWirecardPGIdeal
 *
 * iDEAL Transaction model
 *
 * @since 1.0.0
 */
class ModelExtensionPaymentWirecardPGIdeal extends ModelExtensionPaymentGateway {

	/**
	 * @var string
	 * @since 1.0.0
	 */
	protected $type = 'ideal';

	/**
	 * Basic getMethod method
	 *
	 * @param $address
	 * @param $total
	 * @return array
	 * @since 1.0.0
	 */
	public function getMethod($address, $total) {
		$methodData = parent::getMethod($address, $total);
		$this->load->language('extension/payment/wirecard_pg_' . $this->type);

		$prefix = $this->prefix . $this->type;
		$logo = '<img src="./image/catalog/wirecard_pg_'. $this->type .'.png" width="45" style="margin: 0 22px" />';
		$code = $this->language->get('code');
		if (isset($code) && isset($this->config->get($prefix . '_title' )[$code])) {
			$title = $logo . ' ' . $this->config->get($prefix . '_title' )[$code];
		} else {
			$title = $logo . ' ' . $this->config->get($prefix . '_title' )['en'];
		}

		$methodData['title'] = $title;

		return $methodData;
	}

	/**
	 * Get all valid iDEAL BICs.
	 *
	 * @return array
	 */
	public function getIdealBics() {
		return array(
			array(
				'key'   => IdealBic::ABNANL2A,
				'label' => 'ABN Amro Bank',
			),
			array(
				'key'   => IdealBic::ASNBNL21,
				'label' => 'ASN Bank',
			),
			array(
				'key'   => IdealBic::BUNQNL2A,
				'label' => 'bunq',
			),
			array(
				'key'   => IdealBic::INGBNL2A,
				'label' => 'ING',
			),
			array(
				'key'   => IdealBic::KNABNL2H,
				'label' => 'Knab',
			),
			array(
				'key'   => IdealBic::RABONL2U,
				'label' => 'Rabobank',
			),
			array(
				'key'   => IdealBic::RGGINL21,
				'label' => 'Regio Bank',
			),
			array(
				'key'   => IdealBic::SNSBNL2A,
				'label' => 'SNS Bank',
			),
			array(
				'key'   => IdealBic::TRIONL2U,
				'label' => 'Triodos Bank',
			),
			array(
				'key'   => IdealBic::FVLBNL22,
				'label' => 'Van Lanschot Bankiers',
			),
		);
	}
}
