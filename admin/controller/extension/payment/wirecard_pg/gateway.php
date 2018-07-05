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

include_once(DIR_SYSTEM . 'library/autoload.php');
include_once(__DIR__ . '/../../../../../catalog/model/extension/payment/wirecard_pg/helper/pg_logger.php');
require_once(__DIR__ . '/language_helper.php');

/**
 * Class ControllerExtensionPaymentGateway
 *
 * Basic payment extension controller
 *
 * @since 1.0.0
 */
abstract class ControllerExtensionPaymentGateway extends Controller {

	/**
	 * @var string
	 * @since 1.0.0
	 */
	protected $type;

	/**
	 * @var bool
	 * @since 1.0.0
	 */
	protected $hasPaymentActions = false;

	/**
	 * @var string
	 * @since 1.0.0
	 */
	protected $prefix = 'payment_wirecard_pg_';

	/**
	 * @var array
	 * @since 1.0.0
	 */
	protected $default = array();

	/**
	 * Get a logger instance
	 *
	 * @return PGLogger
	 * @since 1.0.0
	 */
	protected function getLogger() {
		return new PGLogger($this->config);
	}

	/**
	 * @var array
	 * @since 1.0.0
	 */
	protected $configFields = array(
		'status',
		'merchant_account_id',
		'merchant_secret',
		'base_url',
		'http_user',
		'http_password',
		'payment_action',
		'descriptor',
		'additional_info',
		'delete_cancel_order',
		'delete_failure_order',
	);

	/**
	 * @var array
	 * @since 1.0.0
	 */
	protected $multiLangConfigFields = array(
		'title'
	);

	/**
	 * Load common headers and template file including config values
	 *
	 * @since 1.0.0
	 */
	public function index() {
		$this->load->language('extension/payment/wirecard_pg');
		$this->load->language('extension/payment/wirecard_pg_' . $this->type );

		$this->load->model('setting/setting');

		$this->document->setTitle($this->language->get('heading_title'));

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting($this->prefix . $this->type, $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true));
		}

		// prefix for payment type
		$data['prefix'] = $this->prefix . $this->type . '_';
		$data['type'] = $this->type;
		$data['has_payment_actions'] = $this->hasPaymentActions;

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$data['action'] = $this->url->link('extension/payment/wirecard_pg_' . $this->type, 'user_token=' . $this->session->data['user_token'], true);
		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true);
		$data['user_token'] = $this->session->data['user_token'];

		$data = array_merge(
			$data,
			$this->createBreadcrumbs(),
			$this->getConfigText(),
			$this->getRequestData()
		);
		$data = array_merge(
			$this->loadConfigBlocks($data),
			$this->loadLiveChat($data)
		);

		$this->response->setOutput($this->load->view('extension/payment/wirecard_pg', $data));
	}

	/**
	 * Get text for config fields
	 *
	 * @param array $fields
	 * @return mixed
	 * @since 1.0.0
	 */
	protected function getConfigText($fields = []) {
		return $this->getLanguageFields(array_merge($this->getDefaultLanguageFields(), $fields));
	}

	/**
	 * Create breadcrumbs
	 *
	 * @return mixed
	 * @since 1.0.0
	 */
	protected function createBreadcrumbs() {
		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/payment/wirecard_pg_' . $this->type, 'user_token=' . $this->session->data['user_token'], true)
		);

		return $data;
	}

	/**
	 * Set data fields or load config
	 *
	 * @return array
	 * @since 1.0.0
	 */
	protected function getRequestData() {
		$data = array();

		foreach ($this->configFields as $configField) {
			$data[$configField] = $this->getConfigVal($configField);
		}

		return $data;
	}

	/**
	 * Validate specific fields
	 *
	 * @return bool
	 * @since 1.0.0
	 */
	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/payment/wirecard_pg_' . $this->type )) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}

	/**
	 * Test payment specific credentials
	 *
	 * @since 1.0.0
	 */
	public function testConfig() {
		$this->load->language('extension/payment/wirecard_pg');

		$logger = $this->getLogger();
		$json = array();

		$baseUrl = $this->request->post['base_url'];
		$httpUser = $this->request->post['http_user'];
		$httpPass = $this->request->post['http_pass'];

		$testConfig = new \Wirecard\PaymentSdk\Config\Config($baseUrl, $httpUser, $httpPass);
		$transactionService = new \Wirecard\PaymentSdk\TransactionService($testConfig, $logger);
		try {
			$result = $transactionService->checkCredentials();
			if($result) {
				$json['configMessage'] = $this->language->get('success_credentials');
			} else {
				$json['configMessage'] =$this->language->get('error_credentials');
			}
		} catch (\Exception $exception) {
			$json['configMessage'] = $this->language->get('error_credentials');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	/**
	 * Load template blocks for the config
	 *
	 * @param array $data
	 * @return array
	 * @since 1.0.0
	 */
	public function loadConfigBlocks($data) {
		$languageHelper = new ControllerExtensionPaymentWirecardPGLanguageHelper($this->registry);

		$data['payment_header'] = $this->load->view('extension/payment/wirecard_pg/header', $data);
		$data['basic_config'] = $this->load->view('extension/payment/wirecard_pg/basic_config',
			array_merge($data, $languageHelper->getConfigFields($this->multiLangConfigFields, $this->prefix, $this->type, $this->default)));
		$data['credentials_config'] = $this->load->view('extension/payment/wirecard_pg/credentials_config', $data);
		$data['advanced_config'] = $this->load->view('extension/payment/wirecard_pg/advanced_config', $data);

		return $data;
	}

	/**
	 * Load template block for live chat.
	 *
	 * @param array $data
	 * @return mixed
	 * @since 1.0.0
	 */
	public function loadLiveChat($data) {
		$data['live_chat'] = $this->load->view('extension/wirecard_pg/live_chat', $data);

		return $data;
	}

	/**
	 * Check for post or read the value from the config or "default"
	 *
	 * @param string $key
	 * @return array
	 * @since 1.0.0
	 */
	private function getConfigVal($key) {
		$prefix = $this->prefix . $this->type . '_';

		if (isset($this->request->post[$key])) {
			return $this->request->post[$prefix . $key];
		} else {
			return strlen($this->config->get($prefix . $key)) ? $this->config->get($prefix . $key) : $this->default[$key];
		}
	}

	/**
	 * Return language fields
	 *
	 * @param array $configFieldTexts
	 * @return array
	 * @since 1.0.0
	 */
	private function getLanguageFields($configFieldTexts) {
		foreach ($configFieldTexts as $fieldText) {
			$data[$fieldText] = $this->language->get($fieldText);
		}

		return $data;
	}

	/**
	 * Return language codes
	 *
	 * @return array
	 * @since 1.0.0
	 */
	private function getDefaultLanguageFields() {
		return array(
			'text_enabled',
			'text_disabled',
			'config_status',
			'config_title',
			'config_title_desc',
			'config_status_desc',
			'config_merchant_account_id',
			'config_merchant_account_id_desc',
			'config_merchant_secret',
			'config_merchant_secret_desc',
			'config_base_url',
			'config_base_url_desc',
			'config_http_user',
			'config_http_user_desc',
			'config_http_password',
			'config_http_password_desc',
			'text_advanced',
			'text_credentials',
			'test_credentials',
			'config_descriptor',
			'config_descriptor_desc',
			'config_additional_info',
			'config_additional_info_desc',
			'config_payment_action',
			'text_payment_action_pay',
			'text_payment_action_reserve',
			'config_payment_action_desc',
			'config_session_string_desc',
			'config_sort_order',
			'config_sort_order_desc',
			'config_delete_cancel_order_desc',
			'config_delete_failure_order',
			'config_delete_failure_order_desc'
		);
	}
}
