<?php
/**
* Shop System Plugins:
* - Terms of Use can be found under:
* https://github.com/wirecard/opencart-ee/blob/master/_TERMS_OF_USE
* - License can be found under:
* https://github.com/wirecard/opencart-ee/blob/master/LICENSE
*/

include_once(DIR_SYSTEM . 'library/autoload.php');

/**
 * Class ControllerExtensionModuleWirecardPGPGResponseMapper
 *
 * Mapping response array
 *
 * @since 1.1.0
 */
class ControllerExtensionModuleWirecardPGPGResponseMapper extends Controller {

	/**
	 * @var \Wirecard\PaymentSdk\Response\SuccessResponse
	 */
	private $response;

	/**
	 * @var array
	 */
	private $settings;

	public function __construct($registry, $xml) {
		$this->registry = $registry;
		$this->load->language('extension/module/wirecard_pg');
		$this->response = new Wirecard\PaymentSdk\Response\SuccessResponse(simplexml_load_string($xml));
		$this->setSettings();
	}

	/**
	 * Get transaction details
	 * @return array
	 * @since 1.1.0
	 */
	public function getTransactionDetails() {
		return array(
			'data' => $this->response->getTransactionDetails()->getAsHtml($this->settings),
			'title' => $this->language->get('transaction_details_title'),
			'icon' => 'fa-table',
			'type' => 'transaction_details',
		);
	}

	/**
	 * Get Account holder data
	 * @return array
	 * @since 1.1.0
	 */
	public function getAccountHolder() {
		return array(
			'data' => $this->response->getAccountHolder()->getAsHtml($this->settings),
			'title' => $this->language->get('account_holder_title'),
			'icon' => 'fa-user',
			'type' => 'account_holder',
		);
	}

	/**
	 * Get shipping data
	 * @return array
	 * @since 1.1.0
	 */
	public function getShipping() {
		return array(
			'data' => $this->response->getShipping()->getAsHtml($this->settings),
			'title' => $this->language->get('shipping_title'),
			'icon' => 'fa-truck',
			'type' => 'shipping',
		);
	}

	/**
	 * Get basic transaction data
	 * @return array
	 * @since 1.1.0
	 */
	public function getBasicDetails() {
		return array(
			'data' => $this->response->getPaymentDetails()->getAsHtml($this->settings),
			'title' => $this->language->get('payment_details_title'),
			'icon' => 'fa-info-circle',
			'type' => 'basic_info',
		);
	}

	/**
	 * Get basket data
	 * @return array
	 * @since 1.1.0
	 */
	public function getBasket() {
		return array(
			'data' => $this->response->getBasket()->getAsHtml($this->settings),
			'title' => $this->language->get('basket_title'),
			'icon' => '',
			'type' => 'basket',
		);
	}

	/**
	 * Get credit card data
	 * @return array
	 * @since 1.1.0
	 */
	public function getCard() {
		return array(
			'data' => $this->response->getCard()->getAsHtml($this->settings),
			'title' => $this->language->get('credit_card_title'),
			'icon' => 'fa-credit-card',
		);
	}

	/**
	 * Prepare settings for sdk
	 * @since 1.1.0
	 */
	private function setSettings() {
		$this->settings = array(
			'table_class' => 'table',
			'translations' => $this->loadLanguage()
		);
	}

	/**
	 * Return array of translations
	 * @return array
	 */
	private function loadLanguage() {
		$data = [];
		foreach ($this->getLanguageLines() as $line) {
			$data[$line] = $this->language->get($line);
		}

		return $data;
	}

	/**
	 * Return all language keys
	 * @return array
	 */
	private function getLanguageLines() {
		return array(
			'maskedPan',
			'token',
			'name',
			'quantity',
			'amount',
			'description',
			'article-number',
			'account_holder_title',
			'last-name',
			'first-name',
			'email',
			'date-of-birth',
			'phone',
			'street1',
			'street2',
			'city',
			'country',
			'postal-code',
			'house-extension',
			'merchant-crm-id',
			'gender',
			'social-security-number',
			'shipping-method',
			'maid',
			'transactionID',
			'requestId',
			'transactionType',
			'transactionState',
			'requestedAmount',
			'descriptor',
			'paymentMethod',
			'timeStamp',
			'customerId',
			'ip',
			'orderNumber'
		);
	}
}
