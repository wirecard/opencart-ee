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
			$sepaController = $this->getSepaController();
			return $sepaController->getConfig($currency);
		}

		$merchant_account_id = $this->getShopConfigVal('merchant_account_id');
		$merchant_secret = $this->getShopConfigVal('merchant_secret');

		$config = parent::getConfig($currency);
		$paymentConfig = new PaymentMethodConfig(IdealTransaction::NAME, $merchant_account_id, $merchant_secret);
		$config->add($paymentConfig);

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
	 * @param array $parentTransaction
	 * @param \Wirecard\PaymentSdk\Entity\Amount $amount
	 * @return \Wirecard\PaymentSdk\Transaction\Transaction
	 * @since 1.0.0
	 */
	public function createTransaction($parentTransaction, $amount) {
		if ($this->operation == Operation::CREDIT) {
			$this->transaction = new SepaTransaction();
		} else {
			$this->transaction = new IdealTransaction();
		}

		return parent::createTransaction($parentTransaction, $amount);
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

