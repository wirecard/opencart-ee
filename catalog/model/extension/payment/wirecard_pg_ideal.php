<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/opencart-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/opencart-ee/blob/master/LICENSE
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
