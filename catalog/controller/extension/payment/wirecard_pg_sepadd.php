<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/opencart-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/opencart-ee/blob/master/LICENSE
 */

require_once(dirname(__FILE__) . '/wirecard_pg/gateway.php');

use Wirecard\PaymentSdk\Transaction\SepaTransaction;
use Wirecard\PaymentSdk\Config\PaymentMethodConfig;
use Wirecard\PaymentSdk\Transaction\Operation;

/**
 * Class ControllerExtensionPaymentWirecardPGSepaDD
 *
 * SepaDirectDebit Transaction controller
 *
 * @since 1.1.0
 */
class ControllerExtensionPaymentWirecardPGSepaDD extends ControllerExtensionPaymentGateway {

	/**
	 * @var string
	 * @since 1.1.0
	 */
	protected $type = 'sepadd';

	/**
	 * Basic index method
	 *
	 * @param array $data
	 * @return array
	 * @since 1.1.0
	 */
	public function index($data = null) {
		$this->load->language('extension/payment/wirecard_pg_sepadd');
		//needed lang files
		$data['iban_input'] = $this->language->get('iban_input');
		$data['first_name_input'] = $this->language->get('first_name_input');
		$data['last_name_input'] = $this->language->get('last_name_input');
		$data['sepa_legend'] = $this->language->get('sepa_legend');

		$data['sepa'] = $this->load->view('extension/payment/wirecard_pg_sepadd', $data);
		return parent::index($data);
	}

	/**
	 * Create SepaDirectDebit transaction
	 *
	 * @since 1.1.0
	 */
	public function confirm() {
		$this->transaction = $this->getTransactionInstance();

		$json = ['popup' => 'yes'];
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
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
		$payment_config = new PaymentMethodConfig(SepaTransaction::NAME, $merchant_account_id, $merchant_secret);
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

		return $this->model_extension_payment_wirecard_pg_sepadd;
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
	 * @return SepaTransaction
	 * @since 1.0.0
	 */
	public function getTransactionInstance() {
		return new SepaTransaction();
	}
}

