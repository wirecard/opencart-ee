<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/opencart-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/opencart-ee/blob/master/LICENSE
 */

require_once(dirname(__FILE__) . '/wirecard_pg/gateway.php');

use Wirecard\PaymentSdk\Transaction\PoiPiaTransaction;
use Wirecard\PaymentSdk\Config\PaymentMethodConfig;

/**
 * Class ControllerExtensionPaymentWirecardPGPoi
 *
 * Payment On Invoice Transaction controller
 *
 * @since 1.1.0
 */
class ControllerExtensionPaymentWirecardPGPoi extends ControllerExtensionPaymentGateway {

	/**
	 * @var string
	 * @since 1.1.0
	 */
	protected $type = 'poi';

	/**
	 * Payment specific model getter
	 *
	 * @return Model
	 * @since 1.1.0
	 */
	public function getModel() {
		$this->load->model('extension/payment/wirecard_pg_' . $this->type);

		return $this->model_extension_payment_wirecard_pg_poi;
	}

	/**
	 * Create Payment On Invoice transaction
	 *
	 * @since 1.1.0
	 */
	public function confirm() {
		$this->transaction = $this->getTransactionInstance();

		parent::confirm();
	}

	/**
	 * Prepare the Payment On Invoice transaction as required.
	 *
	 * @since 1.1.0
	 */
	public function prepareTransaction() {
		parent::prepareTransaction();

		$this->load->model('checkout/order');

		$order = $this->model_checkout_order->getOrder($this->session->data['order_id']);
		$additional_helper = new AdditionalInformationHelper($this->registry, $this->prefix . $this->type, $this->config);

		$this->transaction = $additional_helper->addAccountHolder($this->transaction, $order, false);
	}

	/**
	 * Create payment specific config
	 *
	 * @param array $currency
	 * @return \Wirecard\PaymentSdk\Config\Config
	 * @since 1.1.0
	 */
	public function getConfig($currency = null) {
		$merchant_account_id = $this->getShopConfigVal('merchant_account_id');
		$merchant_secret = $this->getShopConfigVal('merchant_secret');

		$config = parent::getConfig($currency);
		$payment_config = new PaymentMethodConfig(PoiPiaTransaction::NAME, $merchant_account_id, $merchant_secret);
		$config->add($payment_config);

		return $config;
	}

	/**
	 * Create payment method specific transaction.
	 *
	 * @param array $parent_transaction
	 * @param \Wirecard\PaymentSdk\Entity\Amount $amount
	 * @return \Wirecard\PaymentSdk\Transaction\Transaction
	 * @since 1.1.0
	 */
	public function createTransaction($parent_transaction, $amount) {
		$this->transaction = $this->getTransactionInstance();

		return parent::createTransaction($parent_transaction, $amount);
	}

	/**
	 * Get new instance of payment specific transaction
	 *
	 * @return PoiPiaTransaction
	 * @since 1.1.0
	 */
	public function getTransactionInstance() {
		return new PoiPiaTransaction();
	}

	/**
	 * Adds the payment details to the automatically generated
	 *
	 * @param \Wirecard\PaymentSdk\Response\SuccessResponse $response
	 * @param $order_id
	 * @param $order_state
	 * @return array
	 * @since 1.1.0
	 */
	public function addBankDetailsToInvoice($response, $order_id, $order_state) {
		$this->load->model('checkout/order');
		$this->load->language('extension/payment/wirecard_pg_poipia');

		$response_data = $response->getData();
		$data = [
			'transaction' => [
				'amount' => $this->currency->format($response_data['requested-amount'], $response_data['currency']),
				'iban' => $response_data['merchant-bank-account.0.iban'],
				'bic' => $response_data['merchant-bank-account.0.bic'],
				'ptrid' => $response_data['provider-transaction-reference-id'],
			],

			'texts' => [
				'transfer_notice' => $this->language->get('transfer_notice'),
				'amount' => $this->language->get('amount'),
				'iban' => $this->language->get('iban'),
				'bic' => $this->language->get('bic'),
				'ptrid' => $this->language->get('ptrid'),
			]
		];

		$view = preg_replace("/\r|\n/", "", $this->load->view('extension/payment/wirecard_wiretransfer_notice', $data));
		$this->model_checkout_order->addOrderHistory(
			$order_id,
			$order_state,
			$view,
			false
		);

		if ($this->getShopConfigVal('details_on_invoice')) {
			$order = $this->model_checkout_order->getOrder($order_id);
			$order_comment = ($order['comment']) ? $order['comment'] . '<hr>' : '';
			$order_comment .= $view;

			$this->db->query("
				UPDATE `" . DB_PREFIX . "order`
				SET comment = '" . $this->db->escape($order_comment) . "'
				WHERE order_id = '" . (int)$order_id . "'
			");
		}

		return $data;
	}
}

