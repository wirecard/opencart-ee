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
 * Class ControllerExtensionPaymentWirecardPGPia
 *
 * Payment In Advance Transaction controller
 *
 * @since 1.1.0
 */
class ControllerExtensionPaymentWirecardPGPia extends ControllerExtensionPaymentGateway {

	/**
	 * @var string
	 * @since 1.1.0
	 */
	protected $type = 'pia';

	/**
	 * Payment specific model getter
	 *
	 * @return Model
	 * @since 1.1.0
	 */
	public function getModel() {
		$this->load->model('extension/payment/wirecard_pg_' . $this->type);

		return $this->model_extension_payment_wirecard_pg_pia;
	}

	/**
	 * Create Payment In Advance transaction
	 *
	 * @since 1.1.0
	 */
	public function confirm() {
		$this->transaction = $this->getTransactionInstance();

		parent::confirm();
	}

	/**
	 * Prepare the Payment In Advance transaction as required.
	 *
	 * @param $force_data
	 * @since 1.1.0
	 */
	public function prepareTransaction($force_data = false) {
		parent::prepareTransaction($force_data);

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
	 * Generates a custom success page to show the necessary payment details.
	 *
	 * @param \Wirecard\PaymentSdk\Response\SuccessResponse $result
	 * @return array
	 * @since 1.1.0
	 */
	public function generateSuccessPage($result) {
		$this->load->language('checkout/success');
		$this->load->language('extension/payment/wirecard_pg_poipia');

		$this->cart->clear();
		$this->document->setTitle($this->language->get('heading_title'));

		$response_data = $result->getData();
		$data = [
			'breadcrumbs' => $this->getCheckoutSuccessBreadcrumbs(),
			'pia' => [
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
			]
		];

		$data = array_merge($this->getCommonBlocks(), $data);
		$this->response->setOutput($this->load->view('extension/payment/wirecard_wiretransfer_success', $data));

		return $data;
	}

	/**
	 * Get required breadcrumbs for checkout success
	 *
	 * @return array
	 * @since 1.1.0
	 */
	public function getCheckoutSuccessBreadcrumbs() {
		return [
			[
				'text' => $this->language->get('text_home'),
				'href' => $this->url->link('common/home')
			],
			[
				'text' => $this->language->get('text_basket'),
				'href' => $this->url->link('checkout/cart')
			],
			[
				'text' => $this->language->get('text_checkout'),
				'href' => $this->url->link('checkout/checkout', '', true)
			],
			[
				'text' => $this->language->get('text_success'),
				'href' => $this->url->link('checkout/success')
			],
		];
	}
}

