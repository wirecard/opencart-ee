<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/opencart-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/opencart-ee/blob/master/LICENSE
 */

require_once(dirname(__FILE__) . '/wirecard_pg/gateway.php');

use Wirecard\PaymentSdk\Transaction\PayPalTransaction;
use Wirecard\PaymentSdk\Config\PaymentMethodConfig;

/**
 * Class ControllerExtensionPaymentWirecardPGPayPal
 *
 * PayPal Transaction controller
 *
 * @since 1.0.0
 */
class ControllerExtensionPaymentWirecardPGPayPal extends ControllerExtensionPaymentGateway {

	/**
	 * @var string
	 * @since 1.0.0
	 */
	protected $type = 'paypal';

	/**
	 * Basic index method
	 *
	 * @since 1.0.0
	 */
	public function index($data = null) {
		return parent::index();
	}

	/**
	 * Create paypal transaction
	 *
	 * @since 1.0.0
	 */
	public function confirm() {
		$this->transaction = new PayPalTransaction();

		parent::confirm();
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
		$payment_config = new PaymentMethodConfig(PayPalTransaction::NAME, $merchant_account_id, $merchant_secret);
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

		return $this->model_extension_payment_wirecard_pg_paypal;
	}

	/**
	 * Get new instance of payment specific transaction
	 *
	 * @return PayPalTransaction
	 * @since 1.0.0
	 */
	public function getTransactionInstance() {
		return new PayPalTransaction();
	}

	/**
	 * Create Paypal transaction
	 *
	 * @param array $parentTransaction
	 * @param \Wirecard\PaymentSdk\Entity\Amount $amount
	 * @param string $operation
	 * @return \Wirecard\PaymentSdk\Transaction\Transaction
	 * @since 1.0.0
	 */
	public function createTransaction($parentTransaction, $amount) {
		$this->transaction = new PayPalTransaction();

		return parent::createTransaction($parentTransaction, $amount);
	}

	/**
	 * Prepare PayPal transaction
	 *
	 * @since 1.2.0
	 */
	public function prepareTransaction() {
		$this->load->model('checkout/order');

		$order = $this->model_checkout_order->getOrder($this->session->data['order_id']);
		$additional_helper = new AdditionalInformationHelper($this->registry, $this->prefix . $this->type, $this->config, $this->scale);

		$this->transaction = $additional_helper->addAccountHolder($this->transaction, $order);

		parent::prepareTransaction();
	}
}

