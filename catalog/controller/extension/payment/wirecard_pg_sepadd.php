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
use Wirecard\PaymentSdk\Config\SepaConfig;

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

		$data['iban_input'] = $this->language->get('iban_input');
		$data['first_name_input'] = $this->language->get('first_name_input');
		$data['last_name_input'] = $this->language->get('last_name_input');
		$data['sepa_legend'] = $this->language->get('sepa_legend');
		$data['show_bic'] = false;
		if ($this->getShopConfigVal('enable_bic')) {
			$data['show_bic'] = true;
			$data['bic_input'] = $this->language->get('bic_input');
		}

		$data['sepa'] = $this->load->view('extension/payment/wirecard_pg_sepadd', $data);
		return parent::index($data);
	}

	/**
	 * Create SepaDirectDebit transaction
	 *
	 * @since 1.1.0
	 */
	public function confirm() {
		if ($this->request->post['mandate_confirmed'] == false) {

			$json = ['popup' => $this->generateMandateTemplate($this->request->post)];
			$this->response->addHeader('Content-Type: application/json');
			$this->response->setOutput(json_encode($json));
		} else {
			$this->transaction = $this->getTransactionInstance();
			parent::confirm();
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
		$payment_config = new SepaConfig($merchant_account_id, $merchant_secret);
		$payment_config->setCreditorId($this->getShopConfigVal('creditor_id'));
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

		return $this->model_extension_payment_wirecard_pg_sepadd;
	}

	/**
	 * Get new instance of payment specific transaction
	 *
	 * @return SepaTransaction
	 * @since 1.1.0
	 */
	public function getTransactionInstance() {
		return new SepaTransaction();
	}

	/**
	 * Generate ID for SEPA
	 * @return string
	 * @since 1.1.0
	 */
	private function generateID() {
		return $this->getShopConfigVal('creditor_id') . strtotime(date('Y-m-d H:i:s'));
	}

	/**
	 * Generate template for SEPA mandate
	 * @param array $formData
	 * @return array
	 * @since 1.1.0
	 */
	private function generateMandateTemplate($formData) {
		$this->load->model('checkout/order');
		$order = $this->model_checkout_order->getOrder($this->session->data['order_id']);

		$data['consumer_firstname'] = $formData['first_name'];
		$data['consumer_lastname'] = $formData['last_name'];
		$data['consumer_address'] = $order['payment_address_1'];
		$data['consumer_city'] = $order['payment_city'];
		$data['consumer_post'] = $order['payment_postcode'];
		$data['consumer_country'] = $order['payment_country'];
		$data['consumer_iban'] = $formData['iban'];
		$data['consumer_bic'] = $formData['bic'];

		$data['creditor_id'] = $this->getShopConfigVal('creditor_id');
		$data['creditor_name'] = $this->getShopConfigVal('creditor_name');
		$data['creditor_city'] = $this->getShopConfigVal('creditor_city');
		$data['creditor_date'] = date( 'd.m.Y' );

		return $this->load->view('extension/payment/wirecard_pg_sepa_mandate', $data);
	}
}

