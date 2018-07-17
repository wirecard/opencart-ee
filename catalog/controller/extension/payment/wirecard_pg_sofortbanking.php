<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/opencart-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/opencart-ee/blob/master/LICENSE
 */

require_once(dirname(__FILE__) . '/wirecard_pg/gateway.php');
require_once(dirname(__FILE__) . '/wirecard_pg_sepact.php');

use Wirecard\PaymentSdk\Transaction\SofortTransaction;
use Wirecard\PaymentSdk\Transaction\SepaTransaction;
use Wirecard\PaymentSdk\Config\PaymentMethodConfig;
use Wirecard\PaymentSdk\Transaction\Operation;

/**
 * Class ControllerExtensionPaymentWirecardPGSofortbanking
 *
 * Sofort. Transaction controller
 *
 * @since 1.0.0
 */
class ControllerExtensionPaymentWirecardPGSofortbanking extends ControllerExtensionPaymentGateway {

	/**
	 * @var string
	 * @since 1.0.0
	 */
	protected $type = 'sofortbanking';

	/**
	 * Basic index method
	 *
	 * @since 1.0.0
	 */
	public function index($data = null) {
		return parent::index();
	}

	/**
	 * Create Sofort. transaction
	 *
	 * @since 1.0.0
	 */
	public function confirm() {
		$this->transaction = new SofortTransaction();
		$this->prepareTransaction();

		parent::confirm();
	}

	/**
	 * Provides a SEPA Credit Transfer controller for refunding payments.
	 *
	 * @return ControllerExtensionPaymentWirecardPGSepaCT
	 */
	public function getSepaController() {
		$this->controller_extension_payment_wirecard_pg_sepact = new ControllerExtensionPaymentWirecardPGSepaCT($this->registry);

		return $this->controller_extension_payment_wirecard_pg_sepact;
	}

	/**
	 * Create payment specific config
	 *
	 * @param array $currency
	 * @return \Wirecard\PaymentSdk\Config\Config
	 * @since 1.0.0
	 */
	public function getConfig($currency = null) {
		if ($this->operation == Operation::CREDIT) {
			$sepa_controller = $this->getSepaController();
			return $sepa_controller->getConfig($currency);
		}

		$merchant_account_id = $this->getShopConfigVal('merchant_account_id');
		$merchant_secret = $this->getShopConfigVal('merchant_secret');

		$config = parent::getConfig($currency);
		$payment_config = new PaymentMethodConfig(SofortTransaction::NAME, $merchant_account_id, $merchant_secret);
		$config->add($payment_config);

		return $config;
	}

	/**
	 * Payment specific model getter
	 *
	 * @return Model
	 * @since 1.0.0
	 */
	public function getModel() {
		$this->load->model('extension/payment/wirecard_pg_' . $this->type);

		return $this->model_extension_payment_wirecard_pg_sofortbanking;
	}

	/**
	 * Create Sofort. transaction
	 *
	 * @param array $parentTransaction
	 * @param \Wirecard\PaymentSdk\Entity\Amount $amount
	 * @return \Wirecard\PaymentSdk\Transaction\Transaction
	 * @since 1.0.0
	 */
	public function createTransaction($parentTransaction, $amount) {
		if ($this->operation == Operation::CREDIT) {
			$this->transaction = new SepaTransaction();
		} else {
			$this->transaction = new SofortTransaction();
		}

		return parent::createTransaction($parentTransaction, $amount);
	}

	/**
	 * Get new instance of payment specific transaction
	 *
	 * @return SofortTransaction
	 * @since 1.0.0
	 */
	public function getTransactionInstance() {
		return new SofortTransaction();
	}
}

