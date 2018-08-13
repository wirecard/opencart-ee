<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/opencart-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/opencart-ee/blob/master/LICENSE
 */

require_once(dirname(__FILE__) . '/wirecard_pg/gateway.php');

use Wirecard\PaymentSdk\Transaction\SepaCreditTransferTransaction;
use Wirecard\PaymentSdk\Config\SepaConfig;

/**
 * Class ControllerExtensionPaymentWirecardPGSepaCT
 *
 * SEPA Credit Transfer Transaction controller
 *
 * @since 1.0.0
 */
class ControllerExtensionPaymentWirecardPGSepaCT extends ControllerExtensionPaymentGateway {

	/**
	 * @var string
	 * @since 1.0.0
	 */
	protected $type = 'sepact';

    /**
     * @var string
     * @since 1.1.0
     */
	protected $payment_method = 'sepacredit';

	/**
	 * Basic index method
	 *
	 * @since 1.0.0
	 */
	public function index($data = null) {
		return parent::index();
	}

	/**
	 * Create SEPA Credit Transfer transaction
	 *
	 * @since 1.0.0
	 */
	public function confirm() {
		$this->transaction = new SepaCreditTransferTransaction();

		parent::confirm();
	}

	/**
	 * Create payment specific config
	 *
	 * @param array $currency
	 * @return \Wirecard\PaymentSdk\Config\Config
	 * @since 1.0.0
	 */
	public function getConfig($currency = null) {
		$merchant_account_id = $this->getShopConfigVal('merchant_account_id');
		$merchant_secret = $this->getShopConfigVal('merchant_secret');

		$config = parent::getConfig($currency);
		$payment_config = new SepaConfig($this->payment_method, $merchant_account_id, $merchant_secret);
		$config->add($payment_config);

		return $config;
	}

	/**
	 * Create payment method specific transaction.
	 *
	 * @param array $parentTransaction
	 * @param \Wirecard\PaymentSdk\Entity\Amount $amount
	 * @return \Wirecard\PaymentSdk\Transaction\Transaction
	 */
	public function createTransaction($parentTransaction, $amount) {
		$this->transaction = new SepaCreditTransferTransaction();

		return parent::createTransaction($parentTransaction, $amount);
	}

	/**
	 * Get new instance of payment specific transaction
	 *
	 * @return SepaCreditTransferTransaction
	 * @since 1.0.0
	 */
	public function getTransactionInstance() {
		return new SepaCreditTransferTransaction();
	}
}

