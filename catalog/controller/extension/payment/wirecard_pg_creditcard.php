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

use Wirecard\PaymentSdk\Transaction\CreditCardTransaction;
use Wirecard\PaymentSdk\Entity\Amount;
use Wirecard\PaymentSdk\Config\CreditCardConfig;
use Wirecard\PaymentSdk\TransactionService;

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

		$data['base_url'] = $this->getShopConfigVal('base_url');
		$data['credit_card'] = $this->load->view('extension/payment/wirecard_credit_card_ui', $data);
		return parent::index($data);
	}

	/**
	 * After the order is confirmed in frontend
	 *
	 * @since 1.0.0
	 */
	public function confirm() {
        $this->load->model('checkout/order');
        $this->model_checkout_order->addOrderHistory($this->session->data['order_id'], 1);

		$transactionService = new TransactionService($this->getConfig(), $this->getLogger());
		$response = $transactionService->processJsResponse($_POST, $this->url->link('extension/payment/wirecard_pg_' . $this->type . '/response', '', 'SSL'));

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
		$merchant_account_id = $this->getShopConfigVal('merchant_account_id');
		$merchant_secret = $this->getShopConfigVal('merchant_secret');

		$config = parent::getConfig($currency);
		$paymentConfig = new CreditCardConfig($merchant_account_id, $merchant_secret);

		if ($this->getShopConfigVal('three_d_merchant_account_id') !== '') {
			$paymentConfig->setThreeDCredentials(
				$this->getShopConfigVal('three_d_merchant_account_id'),
				$this->getShopConfigVal('three_d_merchant_secret')
			);
		}

		if ($this->getShopConfigVal('ssl_max_limit') !== '') {
			$paymentConfig->addSslMaxLimit(
				new Amount(
					$this->getShopConfigVal('ssl_max_limit') * $currency['currency_value'],
					$currency['currency_code']
				)
			);
		}

		if ($this->getShopConfigVal('three_d_min_limit') !== '') {
			$paymentConfig->addThreeDMinLimit(
				new Amount(
					$this->getShopConfigVal('three_d_min_limit') * $currency['currency_value'],
					$currency['currency_code']
				)
			);
		}
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
		$this->transaction->setConfig($this->paymentConfig->get(CreditCardTransaction::NAME));
		$this->transaction->setTermUrl($this->url->link('extension/payment/wirecard_pg_' . $this->type . '/response', '', 'SSL'));

		$transactionService = new TransactionService($this->paymentConfig, $this->getLogger());
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(($transactionService->getCreditCardUiWithData(
			$this->transaction,
			$this->getPaymentAction($this->getShopConfigVal('payment_action')),
			'en'
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
}

