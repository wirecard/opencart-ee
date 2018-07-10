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

class ControllerExtensionModuleWirecardPGPGSupportEmail extends Controller {
	const ROUTE = 'extension/payment/wirecard_pg';
	const PREFIX = 'payment_wirecard_pg_';


	public function index() {
		$basicInfo = new ExtensionModuleWirecardPGPluginData();

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');
		$data['user_token'] = $this->session->data['user_token'];
		$data['transaction_overview_link'] = $this->url->link('extension/module/wirecard_pg', 'user_token=' . $this->session->data['user_token'], true);

		$data = array_merge( $data, $basicInfo->getTemplateData(), $this->getBreadcrumbs(), $this->loadText());

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
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$breadcrumbs[] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)
		);

		$breadcrumbs[] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/module/wirecard_pg', 'user_token=' . $this->session->data['user_token'], true)
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
		$basicInfo = new ExtensionModuleWirecardPGPluginData();
		$this->load->model('setting/extension');
		$this->load->model('setting/setting');

		$pluginList = array();
		foreach ($this->getPluginTypes() as $type) {
			$pluginList[$type] =  $this->model_setting_extension->getInstalled($type);
		}

		$pluginConfig = array();
		foreach ($this->getPaymentOptions() as $option) {
			$pluginConfig[$option] = $this->model_setting_setting->getSetting(self::PREFIX . $option);
		}

		$info = array(
			'plugin_name' => $basicInfo->getName(),
			'plugin_version' => $basicInfo->getVersion(),
			'OpenCart_version' => VERSION,
			'installed_plugins' => $pluginList,
			'plugin_config' => $pluginConfig,
			'php_version' => phpversion(),
			'contact_email' => $this->request->post['email'],
			'message' => $this->request->post['message']

		);

		$email_content = print_r($info, true);

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
			"From: " . $sender
		);
	}


	/**
	 * Get lang lines
	 *
	 * @return array
	 */
	private function loadText() {
		$this->load->language(self::ROUTE);

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
