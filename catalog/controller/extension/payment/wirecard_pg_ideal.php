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

use Wirecard\PaymentSdk\Transaction\IdealTransaction;
use Wirecard\PaymentSdk\Transaction\SepaTransaction;
use Wirecard\PaymentSdk\Config\PaymentMethodConfig;
use Wirecard\PaymentSdk\Transaction\Operation;
use Wirecard\PaymentSdk\Entity\IdealBic;

/**
 * Class ControllerExtensionPaymentWirecardPGIdeal
 *
 * iDEAL Transaction controller
 *
 * @since 1.0.0
 */
class ControllerExtensionPaymentWirecardPGIdeal extends ControllerExtensionPaymentGateway {

	/**
	 * @var string
	 * @since 1.0.0
	 */
	protected $type = 'ideal';

	/**
	 * Basic index method
	 *
	 * @since 1.0.0
	 */
	public function index($data = null) {
		$this->load->language('extension/payment/wirecard_pg_' . $this->type);

		$model = $this->getModel();

		$data['bank_label'] = $this->language->get('bank_label');
		$data['ideal_legend'] = $this->language->get('legend');
		$data['ideal_bics'] = $model->getIdealBics();
		$data['ideal'] = $this->load->view('extension/payment/wirecard_ideal_bic', $data);

		return parent::index($data);
	}

	/**
	 * Create iDEAL transaction
	 *
	 * @since 1.0.0
	 */
	public function confirm() {
		$this->transaction = new IdealTransaction();
		$this->prepareTransaction();

		if (isset($this->request->post['ideal-bic'])) {
			$this->transaction->setBic($this->request->post['ideal-bic']);
		}

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
		$payment_config = new PaymentMethodConfig(IdealTransaction::NAME, $merchant_account_id, $merchant_secret);
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

		return $this->model_extension_payment_wirecard_pg_ideal;
	}

	/**
	 * Create iDEAL transaction
	 *
	 * @param array $parent_transaction
	 * @param \Wirecard\PaymentSdk\Entity\Amount $amount
	 * @return \Wirecard\PaymentSdk\Transaction\Transaction
	 * @since 1.0.0
	 */
	public function createTransaction($parent_transaction, $amount) {
		if ($this->operation == Operation::CREDIT) {
			$this->transaction = new SepaTransaction();
		} else {
			$this->transaction = new IdealTransaction();
		}

		return parent::createTransaction($parent_transaction, $amount);
	}

	/**
	 * Get new instance of payment specific transaction
	 *
	 * @return IdealTransaction
	 * @since 1.0.0
	 */
	public function getTransactionInstance() {
		return new IdealTransaction();
	}
}

