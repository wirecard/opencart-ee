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

/**
 * Class ControllerExtensionModuleWirecardPG
 *
 * @since 1.0.0
 */
class ControllerExtensionModuleWirecardPG extends Controller {

	const ROUTE = 'extension/payment/wirecard_pg';

	/**
	 * @var string
	 * @since 1.0.0
	 */
	protected $prefix = 'payment_wirecard_pg_';

	private $error;

	/**
	 * Display transaction panel
	 *
	 * @since 1.0.0
	 */
	public function index() {
		$this->load->language(self::ROUTE);

		$this->document->setTitle($this->language->get('heading_title'));

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		$data['heading_title'] = $this->language->get('heading_title');
		$data['breadcrumbs'] = $this->getBreadcrumbs();

		$data = array_merge($data, $this->getCommons());

		$data['transactions'] = $this->loadTransactionData();

		$this->load->model('setting/setting');
		$this->load->model('setting/extension');
		$this->load->model('user/user_group');

		$this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'extension/module/wirecard_pg/pg_transaction');
		$this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', 'extension/module/wirecard_pg/pg_transaction');

		$this->response->setOutput($this->load->view('extension/wirecard_pg/panel', $data));
	}

	/**
	 * Install process
	 *
	 * @since 1.0.0
	 */
	public function install() {
		$this->load->model('extension/payment/wirecard_pg');
		$this->load->model('localisation/order_status');

		$orderStatus['order_status'][1] = array(
			'name' => 'Authorized'
		);

		$this->model_localisation_order_status->addOrderStatus($orderStatus);

		$this->model_extension_payment_wirecard_pg->install();
	}

	/**
	 * Load transactionlist data
	 *
	 * @since 1.0.0
	 */
	public function loadTransactionData() {
		$this->load->model(self::ROUTE);
		$table = $this->model_extension_payment_wirecard_pg->getTransactionList();

		$transactions = array();
		foreach ($table as $transaction) {
			$this->load->language('extension/payment/wirecard_pg_' . $transaction['payment_method']);
			$title = $this->language->get('heading_title');
			$transactions[] = array(
				'tx_id' => $transaction['tx_id'],
				'order_id' => $transaction['order_id'],
				'transaction_id' => $transaction['transaction_id'],
				'parent_transaction_id' => $transaction['parent_transaction_id'],
				'parent_transaction_href' => $transaction['parent_transaction_id'] ? $this->url->link('extension/module/wirecard_pg/pg_transaction', 'user_token=' . $this->session->data['user_token'] . '&id=' . $transaction['parent_transaction_id'], true) : '',
				'action' => $transaction['transaction_type'],
				'payment_method' => $title,
				'transaction_state' => $transaction['transaction_state'],
				'amount' => $transaction['amount'],
				'currency' => $transaction['currency'],
				'href' => $this->url->link('extension/module/wirecard_pg/pg_transaction', 'user_token=' . $this->session->data['user_token'] . '&id=' . $transaction['transaction_id'], true)
			);
		}

		return $transactions;
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

		return $breadcrumbs;
	}

	/**
	 * Get common live chat, header, sidebar and footer
	 *
	 * @return array
	 * @since 1.0.0
	 */
	public function getCommons() {
		$data['user_token'] = $this->session->data['user_token'];

		$data['live_chat'] = $this->load->view('extension/wirecard_pg/live_chat', $data);

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		return $data;
	}
}
