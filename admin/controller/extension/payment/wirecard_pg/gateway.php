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
		'title',
		'status',
		'merchant_account_id',
		'merchant_secret',
		'base_url',
		'http_user',
		'http_password',
		'payment_action',
		'descriptor',
		'additional_info'
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
		$this->load->model('localisation/order_status');

		$this->document->setTitle($this->language->get('heading_title'));

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting($this->prefix . $this->type, $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true));
		}

		// prefix for payment type
		$data['prefix'] = $this->prefix . $this->type . '_';
		$data['type'] = $this->type;

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$data['action'] = $this->url->link('extension/payment/wirecard_pg_' . $this->type, 'user_token=' . $this->session->data['user_token'], true);
		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true);
		$data['user_token'] = $this->session->data['user_token'];

		$data = array_merge($data, $this->createBreadcrumbs());

		$data = array_merge($data, $this->getConfigText());

		$data = array_merge($data, $this->getRequestData());

		$data = array_merge($data, $this->loadConfigBlocks($data));

		$this->response->setOutput($this->load->view('extension/payment/wirecard_pg', $data));
	}

	/**
	 * Install process
	 *
	 * @since 1.0.0
	 */
	public function install() {
		$this->load->model('extension/payment/wirecard_pg');

		$this->model_extension_payment_wirecard_pg->install();
	}

	/**
	 * Get text for config fields
	 *
	 * @return mixed
	 * @since 1.0.0
	 */
	protected function getConfigText() {
		$data['text_enabled'] = $this->language->get('text_enabled');
		$data['text_disabled'] = $this->language->get('text_disabled');
		$data['config_status'] = $this->language->get('config_status');
		$data['config_title'] = $this->language->get('config_title');
		$data['config_title_desc'] = $this->language->get('config_title_desc');
		$data['config_status_desc'] = $this->language->get('config_status_desc');
		$data['config_merchant_account_id'] = $this->language->get('config_merchant_account_id');
		$data['config_merchant_account_id_desc'] = $this->language->get('config_merchant_account_id_desc');
		$data['config_merchant_secret'] = $this->language->get('config_merchant_secret');
		$data['config_merchant_secret_desc'] = $this->language->get('config_merchant_secret_desc');
		$data['config_base_url'] = $this->language->get('config_base_url');
		$data['config_base_url_desc'] = $this->language->get('config_base_url_desc');
		$data['config_http_user'] = $this->language->get('config_http_user');
		$data['config_http_user_desc'] = $this->language->get('config_http_user_desc');
		$data['config_http_password'] = $this->language->get('config_http_password');
		$data['config_http_password_desc'] = $this->language->get('config_http_password_desc');
		$data['text_advanced'] = $this->language->get('text_advanced');
		$data['text_credentials'] = $this->language->get('text_credentials');
		$data['test_credentials'] = $this->language->get('test_credentials');
		$data['config_descriptor'] = $this->language->get('config_descriptor');
		$data['config_descriptor_desc'] = $this->language->get('config_descriptor_desc');
		$data['config_additional_info'] = $this->language->get('config_additional_info');
		$data['config_additional_info_desc'] = $this->language->get('config_additional_info_desc');
		$data['config_payment_action'] = $this->language->get('config_payment_action');
		$data['text_payment_action_pay'] = $this->language->get('text_payment_action_pay');
		$data['text_payment_action_reserve'] = $this->language->get('text_payment_action_reserve');
		$data['config_payment_action_desc'] = $this->language->get('config_payment_action_desc');
		$data['config_session_string_desc'] = $this->language->get('config_session_string_desc');

		return $data;
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
	protected function loadConfigBlocks($data) {
		$data['payment_header'] = $this->load->view('extension/payment/wirecard_pg/header', $data);
		$data['basic_config'] = $this->load->view('extension/payment/wirecard_pg/basic_config', $data);
		$data['credentials_config'] = $this->load->view('extension/payment/wirecard_pg/credentials_config', $data);
		$data['advanced_config'] = $this->load->view('extension/payment/wirecard_pg/advanced_config', $data);

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
}
