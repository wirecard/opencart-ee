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

include_once(DIR_SYSTEM . '../vendor/autoload.php');

/**
 * Class ControllerExtensionPaymentGateway
 *
 * Basic payment extension controller
 *
 * @since 1.0.0
 */
abstract class ControllerExtensionPaymentGateway extends Controller{

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

		$this->response->setOutput($this->load->view('extension/payment/wirecard_pg', $data));
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
		$data['text_credentials'] = $this->language->get('text_credentials');
		$data['test_credentials'] = $this->language->get('test_credentials');

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
		$prefix = $this->prefix . $this->type . '_';

		if (isset($this->request->post['status'])) {
			$data['status'] = $this->request->post[$prefix . 'status'];
		} else {
			$data['status'] = $this->config->get($prefix . 'status');
		}

		if (isset($this->request->post[$prefix . 'merchant_account_id'])) {
			$data['merchant_account_id'] = $this->request->post[$prefix . 'merchant_account_id'];
		} else {
			$data['merchant_account_id'] = strlen($this->config->get($prefix . 'merchant_account_id')) ? $this->config->get($prefix . 'merchant_account_id') : $this->default['merchant_account_id'];
		}

		if (isset($this->request->post[$prefix . 'merchant_secret'])) {
			$data['merchant_secret'] = $this->request->post[$prefix . 'merchant_secret'];
		} else {
			$data['merchant_secret'] = strlen($this->config->get($prefix . 'merchant_secret')) ? $this->config->get($prefix . 'merchant_secret') : $this->default['merchant_secret'];
		}

		if (isset($this->request->post[$prefix . 'base_url'])) {
			$data['base_url'] = $this->request->post[$prefix . 'base_url'];
		} else {
			$data['base_url'] = strlen($this->config->get($prefix . 'base_url')) ? $this->config->get($prefix . 'base_url') : $this->default['base_url'];
		}

		if (isset($this->request->post[$prefix . 'http_user'])) {
			$data['http_user'] = $this->request->post[$prefix . 'http_user'];
		} else {
			$data['http_user'] = strlen($this->config->get($prefix . 'http_user')) ? $this->config->get($prefix . 'http_user') : $this->default['http_user'];
		}

		if (isset($this->request->post[$prefix . 'http_password'])) {
			$data['http_password'] = $this->request->post[$prefix . 'http_password'];
		} else {
			$data['http_password'] = strlen($this->config->get($prefix . 'http_password')) ? $this->config->get($prefix . 'http_password') : $this->default['http_password'];
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

		$json = array();

		$baseUrl = $this->request->post['base_url'];
		$httpUser = $this->request->post['http_user'];
		$httpPass = $this->request->post['http_pass'];

		$testConfig = new \Wirecard\PaymentSdk\Config\Config($baseUrl, $httpUser, $httpPass);
		$transactionService = new \Wirecard\PaymentSdk\TransactionService($testConfig);
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
}
