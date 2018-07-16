<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/opencart-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/opencart-ee/blob/master/LICENSE
 */

include_once(DIR_SYSTEM . 'library/autoload.php');

class ControllerExtensionModuleWirecardPGPGGeneralTerms extends Controller {

	const ROUTE = 'extension/payment/wirecard_pg';
	const PREFIX = 'payment_wirecard_pg_';

	/**
	* Basic index method
	*
	* @since 1.0.0
	*/
	public function index() {
		$basic_info = new ExtensionModuleWirecardPGPluginData();
		$this->load->language(self::ROUTE);

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');
		$data['user_token'] = $this->session->data['user_token'];
		$data['title'] = $this->language->get('terms_of_use');
		$data['transaction_overview_link'] = $this->url->link('extension/module/wirecard_pg', 'user_token=' . $this->session->data['user_token'], true);

		$data = array_merge( $data, $basic_info->getTemplateData(), $this->getBreadcrumbs());

		$this->response->setOutput($this->load->view('extension/wirecard_pg/general_terms', $data));
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
			'text' => $this->language->get('terms_of_use'),
			'href' => $this->url->link('extension/module/wirecard_pg/pg_general_terms', 'user_token=' . $this->session->data['user_token'], true)
		);
		return ['breadcrumbs' => $breadcrumbs];
	}
}
