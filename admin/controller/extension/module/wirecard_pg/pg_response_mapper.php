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
	 * @return array|bool
	 * @since 1.1.0
	 */
	public function getTransactionDetails() {
		if ($this->response->getTransactionDetails()) {
			return array(
				'data' => $this->response->getTransactionDetails()->getAsHtml($this->settings),
				'title' => $this->language->get('transaction_details_title'),
				'icon' => 'fa-table',
				'type' => 'transaction_details',
			);
		}
		return false;
	}

	/**
	 * Get Account holder data
	 * @return array|bool
	 * @since 1.1.0
	 */
	public function getAccountHolder() {
		if ($this->response->getAccountHolder()) {
			return array(
				'data' => $this->response->getAccountHolder()->getAsHtml($this->settings),
				'title' => $this->language->get('account_holder_title'),
				'icon' => 'fa-user',
				'type' => 'account_holder',
			);
		}
		return false;
	}

	/**
	 * Get shipping data
	 * @return array|bool
	 * @since 1.1.0
	 */
	public function getShipping() {
		if ($this->response->getShipping()) {
			return array(
				'data' => $this->response->getShipping()->getAsHtml($this->settings),
				'title' => $this->language->get('shipping_title'),
				'icon' => 'fa-truck',
				'type' => 'shipping',
			);
		}
		return false;
	}

	/**
	 * Get basic transaction data
	 * @return array|bool
	 * @since 1.1.0
	 */
	public function getBasicDetails() {
		if ($this->response->getPaymentDetails()) {
			return array(
				'data' => $this->response->getPaymentDetails()->getAsHtml($this->settings),
				'title' => $this->language->get('payment_details_title'),
				'icon' => 'fa-info-circle',
				'type' => 'basic_info',
			);
		}
		return false;
	}

	/**
	 * Get basket data
	 * @return array|bool
	 * @since 1.1.0
	 */
	public function getBasket() {
		if ($this->response->getBasket()) {
			return array(
				'data' => $this->response->getBasket()->getAsHtml($this->settings),
				'title' => $this->language->get('basket_title'),
				'icon' => 'fa-shopping-cart',
				'type' => 'basket',
			);
		}
		return false;
	}

	/**
	 * Get credit card data
	 * @return array|bool
	 * @since 1.1.0
	 */
	public function getCard() {
		if ($this->response->getCard()) {
			return array(
				'data' => $this->response->getCard()->getAsHtml($this->settings),
				'title' => $this->language->get('credit_card_title'),
				'icon' => 'fa-credit-card',
			);
		}
		return false;
	}

	/**
	 * Prepare settings for sdk
	 * @since 1.1.0
	 */
	private function setSettings() {
		$this->settings = array(
			'table_class' => 'table',
			'paymentMethod' => HTTP_CATALOG . 'image/catalog/wirecard_pg_',
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
