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
 * Credit Card Transaction controller
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
	 * @param array $data
	 * @return array
	 * @since 1.0.0
	 */
	public function index($data = null) {
		$this->load->language('extension/payment/wirecard_pg');

		$model = $this->getModel();
		$vault = $this->getVault();

		if ($this->customer->isLogged()) {
			$cards = $vault->getCards();
			$shipping_data = null;
			$last_shipping_data = $model->getLatestCustomerShipping();

			if (is_array($this->session->data['shipping_address']) && is_array($last_shipping_data)) {
				$shipping_data = array_filter($this->session->data['shipping_address'], function($key) use ($last_shipping_data) {
					return in_array($key, array_keys($last_shipping_data));
				}, ARRAY_FILTER_USE_KEY);
			}

			// I'm explicitly using != instead of !== here to avoid the array being checked for key order.
			// It *should* theoretically be the same, but there's no guarantees.
			$data['vault'] = $this->getShopConfigVal('vault');
			$data['shipping_data_changed'] = $last_shipping_data != $shipping_data && count($cards) == 0;
			$data['allow_changed_shipping'] = $this->getShopConfigVal('allow_changed_shipping');
			$data['existing_cards'] = (!$data['shipping_data_changed'] || $data['allow_changed_shipping']) ? $cards : null;
		}

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
		if (array_key_exists('token', $this->request->post) && strlen($this->request->post['token'])) {
			return $this->confirmTokenBasedTransaction();
		}

		$this->load->model('checkout/order');
		$this->model_checkout_order->addOrderHistory($this->session->data['order_id'], 1);

		$transaction_service = new TransactionService($this->getConfig(), $this->getLogger());
		$response = $transaction_service->processJsResponse($this->request->post,
			$this->url->link('extension/payment/wirecard_pg_' . $this->type . '/response', '', 'SSL'));

		$this->session->data['save_card'] = isset($this->request->post['save_card']) ? $this->request->post['save_card'] : null;

		return $this->processResponse($response, $this->getLogger(), $transaction_service);
	}

	/**
	 * Handles the confirmation flow for one-click checkout transactions.
	 *
	 * @return mixed
	 * @since 1.1.0
	 */
	protected function confirmTokenBasedTransaction() {
		$model = $this->getModel();

		$this->transaction = $this->getTransactionInstance();
		$this->prepareTransaction(true);

		$this->transaction->setConfig($this->payment_config->get(CreditCardTransaction::NAME));
		$this->transaction->setTermUrl($this->url->link('extension/payment/wirecard_pg_' . $this->type . '/response', '', 'SSL'));
		$this->transaction->setTokenId($this->request->post['token']);

		$response = $model->sendRequest($this->payment_config, $this->transaction, $this->getShopConfigVal('payment_action'));
		if (!isset($this->session->data['error'])) {
			//Save pending order
			$this->model_checkout_order->addOrderHistory($this->session->data['order_id'], 1);
		}

		return $this->response->setOutput($response);
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
                    (float)$additional_helper->convert($ssl_max_limit, $currency),
					$currency['currency_code']
				)
			);
		}

		if ($this->getShopConfigVal('three_d_min_limit') !== '') {
			$three_d_min_limit = floatval($this->getShopConfigVal('three_d_min_limit'));
			$payment_config->addThreeDMinLimit(
				new Amount(
                    (float)$additional_helper->convert(
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
	 * @since 1.0.0
	 */
	public function getCreditCardUiRequestData() {
		$this->transaction = new CreditCardTransaction();
		$language = $this->getLocale($this->getShopConfigVal('base_url'));
		$this->prepareTransaction();
		$this->transaction->setConfig($this->payment_config->get(CreditCardTransaction::NAME));
		$this->transaction->setTermUrl($this->url->link('extension/payment/wirecard_pg_' . $this->type . '/response', '', 'SSL'));
		$transaction_service = new TransactionService($this->payment_config, $this->getLogger());
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(($transaction_service->getCreditCardUiWithData(
			$this->transaction,
			$this->getPaymentAction($this->getShopConfigVal('payment_action')),
			$language
		)));
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
	 * Create Credit Card transaction
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

	/**
	 * Delete a Credit Card from the vault.
	 *
	 * @since 1.1.0
	 */
	public function deleteCardFromVault() {
		$vault = $this->getVault();

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode([
			"success" => $vault->deleteCard($this->request->post['card']),
			"deleted_card" => $this->request->post['masked_pan']
		]));
	}

	/**
	 * Get an instance of the Credit Card vault.
	 *
	 * @return ModelExtensionPaymentWirecardPGVault
	 * @since 1.1.0
	 */
	protected function getVault() {
		$this->load->model('extension/payment/wirecard_pg/vault');

		return $this->model_extension_payment_wirecard_pg_vault;
	}
}

