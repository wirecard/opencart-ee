<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/opencart-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/opencart-ee/blob/master/LICENSE
 */

require_once(dirname(__FILE__) . '/wirecard_pg/gateway.php');

use Wirecard\PaymentSdk\Transaction\MaestroTransaction;
use Wirecard\PaymentSdk\Config\MaestroConfig;
use Wirecard\PaymentSdk\TransactionService;

/**
 * Class ControllerExtensionPaymentWirecardPGMaestro
 *
 * Maestro SecureCode Transaction controller
 *
 * @since 1.0.0
 */
class ControllerExtensionPaymentWirecardPGMaestro extends ControllerExtensionPaymentGateway {

	/**
	 * @var string
	 * @since 1.0.0
	 */
	protected $type = 'maestro';

	/**
	 * Basic index method
	 *
	 * @param array $data
	 * @return array
	 * @since 1.0.0
	 */
	public function index($data = null) {
		$this->load->language('extension/payment/wirecard_pg');

		$data['base_url'] = $this->getShopConfigVal('base_url');
		$data['loading_text'] = $this->language->get('loading_text');
		$data['type'] = $this->type;
		$data['credit_card'] = $this->load->view('extension/payment/wirecard_credit_card_ui', $data);

		return parent::index($data);
	}

	/**
	 * After the order is confirmed in frontend
	 *
	 * @return mixed
	 * @since 1.0.0
	 */
	public function confirm() {
		$this->load->model('checkout/order');
		$this->model_checkout_order->addOrderHistory($this->session->data['order_id'], 1);

		$transaction_service = new TransactionService($this->getConfig(), $this->getLogger());
		$response = $transaction_service->processJsResponse($this->request->post,
			$this->url->link('extension/payment/wirecard_pg_' . $this->type . '/response', '', 'SSL'));

		return $this->processResponse($response, $this->getLogger(), $transaction_service);
	}

	/**
	 * Create payment specific config
	 *
	 * @param array $currency
	 * @return \Wirecard\PaymentSdk\Config\Config
	 * @since 1.0.0
	 */
	public function getConfig($currency = null) {
		$config = parent::getConfig($currency);
		$payment_config = new MaestroConfig();

		if ($this->getShopConfigVal('merchant_account_id') !== 'null') {
			$payment_config->setThreeDCredentials(
				$this->getShopConfigVal('merchant_account_id'),
				$this->getShopConfigVal('merchant_secret')
			);
		}

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

		return $this->model_extension_payment_wirecard_pg_maestro;
	}

	/**
	 * Return data via ajax call for the seamless form renderer
	 *
	 * @since 1.0.0
	 */
	public function getMaestroUiRequestData() {
		$this->transaction = new MaestroTransaction();
		$this->prepareTransaction();
		$this->transaction->setConfig($this->payment_config->get(MaestroTransaction::NAME));
		$this->transaction->setTermUrl($this->url->link('extension/payment/wirecard_pg_' . $this->type . '/response', '', 'SSL'));
		$transaction_service = new TransactionService($this->payment_config, $this->getLogger());
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(($transaction_service->getCreditCardUiWithData(
			$this->transaction,
			$this->getPaymentAction($this->getShopConfigVal('payment_action')),
			$this->language->get('code')
		)));
	}

	/**
	 * Get new instance of payment specific transaction
	 *
	 * @return MaestroTransaction
	 * @since 1.0.0
	 */
	public function getTransactionInstance() {
		return new MaestroTransaction();
	}

	/**
	 * Create Maestro SecureCode transaction
	 *
	 * @param array $parentTransaction
	 * @param \Wirecard\PaymentSdk\Entity\Amount $amount
	 * @return \Wirecard\PaymentSdk\Transaction\Transaction
	 * @since 1.0.0
	 */
	public function createTransaction($parentTransaction, $amount) {
		$this->transaction = $this->getTransactionInstance();

		return parent::createTransaction($parentTransaction, $amount);
	}
}

