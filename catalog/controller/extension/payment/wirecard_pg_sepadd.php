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
		$this->load->language('extension/payment/wirecard_pg_sepadd');
		if ((bool)$this->request->post['mandate_confirmed'] == false) {
			$json = [];
			if ($this->validateMadnatoryFields($this->request->post, $this->getShopConfigVal('enable_bic'))) {
				$json = ['popup' => $this->generateMandateTemplate($this->request->post), 'button_text' => $this->language->get('sepa_cancel')];
			} else {
				$json = ['error' => $this->language->get('sepa_fields_error')];
			}

			$this->response->addHeader('Content-Type: application/json');
			$this->response->setOutput(json_encode($json));
		} else {
			$this->transaction = $this->getTransactionInstance();
			parent::confirm();
		}
	}

	/**
	 * Set additional data needed for SEPA
	 *
	 * @since 1.1.0
	 */
	public function prepareTransaction() {
		parent::prepareTransaction();

		$account_holder = new \Wirecard\PaymentSdk\Entity\AccountHolder();
		$account_holder->setFirstName($this->request->post['first_name']);
		$account_holder->setLastName($this->request->post['last_name']);
		$this->transaction->setAccountHolder($account_holder);

		$this->transaction->setIban($this->request->post['iban']);
		if ($this->getShopConfigVal('enable_bic')) {
			$this->transaction->setBic($this->request->post['bic']);
		}

		$mandate = new \Wirecard\PaymentSdk\Entity\Mandate($this->generateId());
		$this->transaction->setMandate($mandate);
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
	private function generateId() {
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

		$data['consumer_first_name'] = $formData['first_name'];
		$data['consumer_last_name'] = $formData['last_name'];
		$data['consumer_address'] = $order['payment_address_1'];
		$data['consumer_iban'] = $formData['iban'];
		$data['customer_bic'] = null;
		if ($this->getShopConfigVal('enable_bic')) {
			$data['consumer_bic'] = $formData['bic'];
		}

		$data['creditor_id'] = $this->getShopConfigVal('creditor_id');
		$data['creditor_name'] = $this->getShopConfigVal('creditor_name');
		$data['creditor_city'] = $this->getShopConfigVal('creditor_city');
		$data['creditor_date'] = date( 'd.m.Y' );
		$code = $this->language->get('code');
		if (isset($code) && isset($this->config->get('payment_wirecard_pg_sepadd_mandate_text')[$code])) {
			$data['additional_text'] = $this->config->get('payment_wirecard_pg_sepadd_mandate_text')[$code];
		}

		array_merge(
			$this->loadLangLines(
				array(
					'creditor',
					'creditor_id',
					'debtor',
					'debtor_acc_owner',
					'sepa_text_1',
					'sepa_text_2',
					'sepa_text_3',
					'sepa_text_4',
					'sepa_text_5',
					'sepa_text_6',
					'sepa_cancel',
					'sepa_mandate'
				)
			),
			$data
		);

		return $this->load->view('extension/payment/wirecard_pg_sepa_mandate', $data);
	}

	/**
	 * Load lang lines
	 *
	 * @param array $lines
	 * @return array
	 * @since 1.1.0
	 */
	private function loadLangLines($lines) {
		$this->load->language('extension/payment/wirecard_pg_sepadd');
		$data = [];
		foreach ($lines as $line) {
			$data[$line] = $this->language->get($line);
		}

		return $data;
	}

	/**
	 * @param array $formFields
	 * @param bool $bic_enabled
	 * @return boolean
	 * @since 1.1.0
	 */
	private function validateMadnatoryFields($formFields, $bic_enabled) {
		$mandatoryFields = array(
			'iban',
			'first_name',
			'last_name',
		);
		if ($bic_enabled) {
			array_push($mandatoryFields, 'bic');
		}

		foreach ($mandatoryFields as $field) {
			if (!array_key_exists($field, $formFields) ||
				$formFields[$field] === '') {
				return false;
			}
		}

		return true;
	}
}

