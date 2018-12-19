<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/opencart-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/opencart-ee/blob/master/LICENSE
 */

require_once(dirname(__FILE__) . '/wirecard_pg/gateway.php');

use Wirecard\PaymentSdk\Transaction\RatepayInvoiceTransaction;
use Wirecard\PaymentSdk\Config\PaymentMethodConfig;

/**
 * Class ControllerExtensionPaymentWirecardPGRatepayInvoice
 *
 * Guaranteed Invoice Transaction controller
 *
 * @since 1.1.0
 */
class ControllerExtensionPaymentWirecardPGRatepayInvoice extends ControllerExtensionPaymentGateway {

	/**
	 * @var string
	 * @since 1.1.0
	 */
	protected $type = 'ratepayinvoice';

	/**
	 * @var int
	 * @since 1.1.0
	 */
	protected $scale = 2;

	/**
	 * Basic index method
	 *
	 * @param array $data
	 * @return array
	 * @since 1.1.0
	 */
	public function index($data = null) {
		$this->load->language('extension/payment/wirecard_pg_ratepayinvoice');
		$data['birthdate_input'] = $this->language->get('birthdate_input');
		$data['birthdate_error'] = $this->language->get('ratepayinvoice_fields_error');

		$data['ratepay_device_ident'] = $this->getRatepayDevice();
		$data['ratepayinvoice'] = $this->load->view('extension/payment/wirecard_pg_ratepayinvoice', $data);
		return parent::index($data);
	}

	/**
	 * Create Guaranteed invoice transaction
	 *
	 * @since 1.1.0
	 */
	public function confirm() {
		$this->transaction = $this->getTransactionInstance();
		parent::confirm();
	}

	/**
	 * Set additional data for Ratepay-Invoice transaction
	 *
	 * @since 1.1.0
	 */
	public function prepareTransaction() {
		parent::prepareTransaction();

		$this->load->model('checkout/order');
		$order = $this->model_checkout_order->getOrder($this->session->data['order_id']);
		$additional_helper = new AdditionalInformationHelper($this->registry, $this->prefix . $this->type, $this->config, $this->scale);
		$currency = $additional_helper->getCurrency($order['currency_code'], $this->type);

        $shipping = false;
        if ($this->cart->hasShipping()) {
            $shipping = $this->session->data['shipping_method'];
        }

		$this->transaction = $additional_helper->addBasket(
			$this->transaction,
			$this->cart->getProducts(),
            $shipping,
			$currency,
			$order['total']
		);
		if (isset($this->request->post['ratepayinvoice-birthdate'])) {
			$this->transaction = $additional_helper->addAccountHolder(
				$this->transaction,
				$order,
				true,
				$this->request->post['ratepayinvoice-birthdate']
			);
		}
		if (isset($this->request->post['ratepayinvoice-device'])) {
			$device_ident = $this->request->post['ratepayinvoice-device'];
			$device = new \Wirecard\PaymentSdk\Entity\Device();
			$device->setFingerprint($device_ident);
			$this->transaction->setDevice($device);
		}
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
		$payment_config = new PaymentMethodConfig(RatepayInvoiceTransaction::NAME, $merchant_account_id, $merchant_secret);
		$config->add($payment_config);

		return $config;
	}

	/**
	 * Payment specific model getter
	 *
	 * @return Model
	 * @since 1.1.0
	 */
	public function getModel() {
		$this->load->model('extension/payment/wirecard_pg_' . $this->type);

		return $this->model_extension_payment_wirecard_pg_ratepayinvoice;
	}

	/**
	 * Create Ratepay-Invoice transaction
	 *
	 * @param array $parent_transaction
	 * @param \Wirecard\PaymentSdk\Entity\Amount $amount
	 * @return \Wirecard\PaymentSdk\Transaction\Transaction
	 * @since 1.1.0
	 */
	public function createTransaction($parent_transaction, $amount) {
		$this->transaction = new RatepayInvoiceTransaction();

		//create basket from response
		$basket_factory = new PGBasket($this);
		$requested_amount = $basket_factory->createBasketFromArray($this->transaction, $parent_transaction);
		$amount = new \Wirecard\PaymentSdk\Entity\Amount($requested_amount, $parent_transaction['currency']);

		return parent::createTransaction($parent_transaction, $amount);
	}

	/**
	 * Get new instance of payment specific transaction
	 *
	 * @return RatepayInvoiceTransaction
	 * @since 1.1.0
	 */
	public function getTransactionInstance() {
		return new RatepayInvoiceTransaction();
	}

	/**
	 * Get Ratepay Device Ident
	 *
	 * @return string
	 * @since 1.1.0
	 */
	private function getRatepayDevice() {
		$merchant_account_id = $this->getShopConfigVal('merchant_account_id');
		return md5($merchant_account_id . '_' . microtime());
	}
}

