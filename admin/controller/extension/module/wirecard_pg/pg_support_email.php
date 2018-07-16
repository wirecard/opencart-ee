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
	const ROUTE = 'extension/payment/wirecard_pg';
	const PREFIX = 'payment_wirecard_pg_';


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
		foreach ($this->getPaymentOptions() as $option) {
			$plugin_config[$option] = $this->model_setting_setting->getSetting(self::PREFIX . $option);
			unset(
				$plugin_config[$option][self::PREFIX . $option . '_merchant_secret'],
				$plugin_config[$option][self::PREFIX . $option . '_merchant_secret'],
				$plugin_config[$option][self::PREFIX . $option . '_three_d_merchant_secret']
			);
		}

		$info = array(
			'plugin_name' => $basic_info->getName(),
			'plugin_version' => $basic_info->getVersion(),
			'opencart_version' => VERSION,
			'installed_plugins' => $plugin_list,
			'plugin_config' => $plugin_config,
			'php_version' => phpversion(),
			'contact_email' => $this->request->post['email'],
			'message' => $this->request->post['message']

		);

		$email_content = $this->load->view('extension/wirecard_pg/email_template', $info);

		if ($this->sendMail($email_content, $this->request->post['email'])) {
			$this->response->setOutput(json_encode(['success' => true]));
		} else {
			$this->response->setOutput(json_encode(['success' => false]));
		}
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
	 */
	private function getPaymentOptions() {
		return array(
			'creditcard',
			'ideal',
			'paypal',
			'sepact',
			'sofortbanking',
			'poi',
			'pia'
		);
	}

	/**
	 * @param string $email_content
	 * @param string $sender
	 * @return bool
	 */
	private function sendMail($email_content, $sender) {
		return mail(
			'shop-systems-support@wirecard.com',
			'OpenCart support request',
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
