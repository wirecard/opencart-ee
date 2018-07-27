<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/opencart-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/opencart-ee/blob/master/LICENSE
 */

require_once(dirname(__FILE__) . '/wirecard_pg/gateway.php');

use Wirecard\PaymentSdk\Transaction\CreditCardTransaction;
use Wirecard\PaymentSdk\Entity\Amount;
use Wirecard\PaymentSdk\Config\CreditCardConfig;
use Wirecard\PaymentSdk\TransactionService;
use Wirecard\PaymentSdk\Exception\MalformedResponseException;

/**
 * Class ControllerExtensionPaymentWirecardPGCreditCard
 *
 * CreditCard Transaction controller
 *
 * @since 1.0.0
 */
class ControllerExtensionPaymentWirecardPGCreditCard extends ControllerExtensionPaymentGateway {

	/**
	 * @var string
	 * @since 1.0.0
	 */
	protected $type = 'creditcard';

	/**
	 * Basic index method
	 *
	 * @since 1.0.0
	 */
	public function index($data = null) {
		$this->load->language('extension/payment/wirecard_pg');

		$vault = $this->getVault();
		$data['existing_cards'] = $vault->getCards($this->customer);
		$data['base_url'] = $this->getShopConfigVal('base_url');
		$data['loading_text'] = $this->language->get('loading_text');
		$data['credit_card'] = $this->load->view('extension/payment/wirecard_credit_card_ui', $data);

		return parent::index($data);
	}

	/**
	 * After the order is confirmed in frontend
	 *
	 * @since 1.0.0
	 */
	public function confirm() {
		var_dump($_POST);
		die();

		$this->load->model('checkout/order');
		$this->model_checkout_order->addOrderHistory($this->session->data['order_id'], 1);
		$this->session->data['save_card'] = isset($this->request->post['save_card']) ? $this->request->post['save_card'] : null;

		$transaction_service = new TransactionService($this->getConfig(), $this->getLogger());
		$response = $transaction_service->processJsResponse($_POST,
			$this->url->link('extension/payment/wirecard_pg_' . $this->type . '/response', '', 'SSL'));

		return $this->processResponse($response, $this->getLogger());
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
		$payment_config = new CreditCardConfig();
		$additional_helper = new AdditionalInformationHelper($this->registry, $this->prefix . $this->type, $this->config);

		if ($this->getShopConfigVal('merchant_account_id') !== 'null') {
			$payment_config->setSSLCredentials(
				$this->getShopConfigVal('merchant_account_id'),
				$this->getShopConfigVal('merchant_secret')
			);
		}

		if ($this->getShopConfigVal('three_d_merchant_account_id') !== 'null') {
			$payment_config->setThreeDCredentials(
				$this->getShopConfigVal('three_d_merchant_account_id'),
				$this->getShopConfigVal('three_d_merchant_secret')
			);
		}

		if ($this->getShopConfigVal('ssl_max_limit') !== '') {
			$ssl_max_limit = floatval($this->getShopConfigVal('ssl_max_limit'));
			$payment_config->addSslMaxLimit(
				new Amount(
					$additional_helper->convert($ssl_max_limit, $currency),
					$currency['currency_code']
				)
			);
		}

		if ($this->getShopConfigVal('three_d_min_limit') !== '') {
			$three_d_min_limit = floatval($this->getShopConfigVal('three_d_min_limit'));
			$payment_config->addThreeDMinLimit(
				new Amount(
					$additional_helper->convert(
						$three_d_min_limit,
						$currency
					),
					$currency['currency_code']
				)
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

		return $this->model_extension_payment_wirecard_pg_creditcard;
	}

	/**
	 * Return data via ajax call for the seamless form renderer
	 *
	 * @return array
	 * @since 1.0.0
	 */
	public function getCreditCardUiRequestData() {
		$this->transaction = new CreditCardTransaction();
		$this->prepareTransaction();
		$this->transaction->setConfig($this->payment_config->get(CreditCardTransaction::NAME));
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
	 * Get payment action
	 *
	 * @param string $action
	 * @return string
	 * @since 1.0.0
	 */
	public function getPaymentAction($action) {
		if ($action == 'pay') {
			return 'purchase';
		} else {
			return 'authorization';
		}
	}

	/**
	 * Get new instance of payment specific transaction
	 *
	 * @return CreditCardTransaction
	 * @since 1.0.0
	 */
	public function getTransactionInstance() {
		return new CreditCardTransaction();
	}

	/**
	 * Create CreditCard transaction
	 *
	 * @param array $parentTransaction
	 * @param \Wirecard\PaymentSdk\Entity\Amount $amount
	 * @return \Wirecard\PaymentSdk\Transaction\Transaction
	 * @since 1.0.0
	 */
	public function createTransaction($parentTransaction, $amount) {
		$this->transaction = new CreditCardTransaction();

		return parent::createTransaction($parentTransaction, $amount);
	}

}

