<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/opencart-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/opencart-ee/blob/master/LICENSE
 */

include_once(DIR_SYSTEM . 'library/autoload.php');

class ControllerExtensionModuleWirecardPGPGSupportEmail extends Controller {
	/** @var string  */
	const ROUTE = 'extension/payment/wirecard_pg';
	/** @var string  */
	const PREFIX = 'payment_wirecard_pg_';
	/** @var string  */
	const SHOP_SYSTEM_SUPPORT_EMAIL = 'shop-systems-support@wirecard.com';
	/** @var string  */
	const SHOP_SYSTEM_DEFAULT_EMAIL_SUBJECT = 'OpenCart support request';

	public function index() {
		$basic_info = new ExtensionModuleWirecardPGPluginData();
		$this->load->language(self::ROUTE);

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');
		$data['user_token'] = $this->session->data['user_token'];
		$data['transaction_overview_link'] = $this->url->link('extension/module/wirecard_pg', 'user_token=' . $this->session->data['user_token'], true);

		$data = array_merge( $data, $basic_info->getTemplateData(), $this->getBreadcrumbs(), $this->loadText());

		$this->response->setOutput($this->load->view('extension/wirecard_pg/support_email', $data));
	}

	/**
	 * Get breadcrumb data
	 *
	 * @return array
	 * @since 1.0.0
	 */
	public function getBreadcrumbs() {
		$breadcrumbs = array();

		$breadcrumbs[] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)
		);

		$breadcrumbs[] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/module/wirecard_pg', 'user_token=' . $this->session->data['user_token'], true)
		);

		$breadcrumbs[] = array(
			'text' => $this->language->get('support_email_title'),
			'href' => $this->url->link('extension/module/wirecard_pg/pg_support_email', 'user_token=' . $this->session->data['user_token'], true)
		);
		return ['breadcrumbs' => $breadcrumbs];
	}

	/**
	 * Send email to support
	 *
	 * @return array
	 * @since 1.0.0
	 */
	public function sendEmail() {
		$basic_info = new ExtensionModuleWirecardPGPluginData();
		$this->load->model('setting/extension');
		$this->load->model('setting/setting');

		$plugin_list = array();
		foreach ($this->getPluginTypes() as $type) {
			$plugin_list[$type] =  $this->model_setting_extension->getInstalled($type);
		}

		$plugin_config = array();
		foreach ($this->getPaymentOptions() as $payment_method_code) {
			$payment_option_list = $this->model_setting_setting->getSetting(self::PREFIX . $payment_method_code);
			$plugin_config[$payment_method_code] = $this->paymentWhiteListedConfig(
				$payment_method_code,
				$payment_option_list
			);
		}

		$info = array(
			'plugin_name' => $basic_info->getName(),
			'plugin_version' => $basic_info->getVersion(),
			'opencart_version' => VERSION,
			'installed_plugins' => $plugin_list,
			'plugin_config' => $plugin_config,
			'php_version' => phpversion(),
			'os' => php_uname(),
			'contact_email' => $this->request->post['email'],
			'message' => $this->request->post['message']

		);

		$email_content = $this->load->view('extension/wirecard_pg/email_template', $info);

		$json = ['success' => false];
		if ($this->sendMail($email_content, $this->request->post['email'])) {
			$json = ['success' => true];
		}

		$this->response->setOutput(json_encode($json));
	}

	/**
	 * Return plugin types to send in support email
	 *
	 * @return array
	 * @since 1.0.0
	 */
	private function getPluginTypes() {
		return array(
			'payment',
			'shipping',
			'total',
			'module',
			'fraud'
		);
	}

	/**
	 * Return payment options
	 *
	 * @return array
	 * @since 1.0.0
	 * @since 1.5.1 Added new payment methods
	 */
	private function getPaymentOptions() {
		return [
			'creditcard',
			'ideal',
			'paypal',
			'sepact',
			'sofortbanking',
			'poi',
			'pia',
			'alipay_crossborder',
			'maestro',
			'masterpass',
			'ratepayinvoice',
			'sepadd',
			'upi'
		];
	}

	/**
	 * Get options whitelist
	 *
	 * @return array
	 * @since 1.5.1
	 */
	private function getWhiteListOptionList() {
		return [
			// General options
			'title',
			'status',
			'sort_order',
			'payment_action',
			'additional_info',
			'base_url',
			'descriptor',
			'delete_cancel_order',
			'delete_failure_order',
			'merchant_account_id',
			// Payment specific options
			'allowed_currencies',
			'allow_changed_shipping',
			'basket_max',
			'basket_min',
			'billing_countries',
			'billing_shipping',
			'challenge_indicator',
			'creditor_city',
			'details_on_invoice',
			'enable_bic',
			'logo_variant',
			'mandate_text',
			'shipping_countries',
			'shopping_basket',
			'ssl_max_limit',
			'three_d_merchant_account_id',
			'three_d_min_limit',
			'vault',
		];
	}

	/**
	 * Get payment method prefix
	 *
	 * @param string $payment_method_code
	 * @return string
	 * @since 1.5.1
	 */
	private function getPaymentMethodPrefixByCode($payment_method_code) {
		return sprintf(
			"%s%s_",
			self::PREFIX,
			$payment_method_code
		);
	}

	/**
	 * Slice string with prefix
	 *
	 * @param string $option
	 * @param string $prefix
	 * @return false|string
	 * @since 1.5.1
	 */
	private function getSlicedOptionByPrefix($option, $prefix) {
		return substr($option, strlen($prefix));
	}

	/**
	 * Sanitize payment config with whitelist options
	 *
	 * @param string $payment_method_code
	 * @param array $unsafe_config
	 * @return array
	 * @since 1.5.1
	 */
	protected function paymentWhiteListedConfig($payment_method_code, $unsafe_config) {
		$payment_method_prefix = $this->getPaymentMethodPrefixByCode($payment_method_code);
		$safe_config = [];
		foreach ($unsafe_config as $payment_option => $payment_option_value) {
			$extracted_option = $this->getSlicedOptionByPrefix($payment_option, $payment_method_prefix);
			if (!strlen($extracted_option) || !in_array($extracted_option, $this->getWhiteListOptionList(), true)) {
				continue;
			}
			$safe_config[$payment_option] = $payment_option_value;
		}
		return $safe_config;
	}

	/**
	 * Send mail
	 *
	 * @param string $email_content
	 * @param string $sender
	 * @return bool
	 * @since 1.5.1
	 */
	private function sendMail($email_content, $sender) {
		return mail(
			self::SHOP_SYSTEM_SUPPORT_EMAIL,
			self::SHOP_SYSTEM_DEFAULT_EMAIL_SUBJECT,
			$email_content,
			"From: " . $sender . "\r\n" . "Content-type: text/html; charset=utf-8\r\n"
		);
	}


	/**
	 * Get lang lines
	 *
	 * @return array
	 */
	private function loadText() {
		$data['config_email'] = $this->language->get('config_email');
		$data['config_message'] = $this->language->get('config_message');
		$data['success_email'] = $this->language->get('success_email');
		$data['error_email'] = $this->language->get('error_email');
		$data['back_button'] = $this->language->get('back_button');
		$data['send_email'] = $this->language->get('send_email');
		$data['support_email_title'] = $this->language->get('support_email_title');

		return $data;
	}
}
