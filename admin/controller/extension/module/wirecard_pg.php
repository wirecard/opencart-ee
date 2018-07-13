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
 * Class ControllerExtensionModuleWirecardPG
 *
 * @since 1.0.0
 */
class ControllerExtensionModuleWirecardPG extends Controller {

	const ROUTE = 'extension/payment/wirecard_pg';
	const PG_TRANSACTION = 'extension/module/wirecard_pg/pg_transaction';
	const PG_SUPPORT_MAIL = 'extension/module/wirecard_pg/pg_support_email';
	const PG_GENERAL_TERMS = 'extension/module/wirecard_pg/pg_general_terms';
	const HEADING_TITLE = 'heading_title';
	const TRANSACTION_ID = 'transaction_id';
	const PARENT_TRANSACTION_ID = 'parent_transaction_id';
	const USER_TOKEN = 'user_token';

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
		$basic_info = new ExtensionModuleWirecardPGPluginData();
		$this->load->language(self::ROUTE);

		$this->document->setTitle($this->language->get(self::HEADING_TITLE));

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		$data[self::HEADING_TITLE] = $this->language->get(self::HEADING_TITLE);
		$data['breadcrumbs'] = $this->getBreadcrumbs();
		$data['support_link'] = $this->url->link('extension/module/wirecard_pg/pg_support_email', 'user_token=' . $this->session->data['user_token'], true);
		$data['general_terms'] = $this->url->link('extension/module/wirecard_pg/pg_general_terms', 'user_token=' . $this->session->data['user_token'], true);

		$data = array_merge($data, $this->getCommons(), $basic_info->getTemplateData());

		$data['transactions'] = $this->loadTransactionData();

		$this->load->model('setting/setting');
		$this->load->model('setting/extension');
		$this->load->model('user/user_group');

		$this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', self::PG_TRANSACTION);
		$this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', self::PG_TRANSACTION);
		$this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', self::PG_SUPPORT_MAIL);
		$this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', self::PG_SUPPORT_MAIL);
		$this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', self::PG_GENERAL_TERMS);
		$this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', self::PG_GENERAL_TERMS);

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

		$order_status['order_status'][1] = array(
			'name' => 'Authorized'
		);

		$this->model_localisation_order_status->addOrderStatus($order_status);

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
			$title = $this->language->get(self::HEADING_TITLE);
			$transactions[] = array(
				'tx_id' => $transaction['tx_id'],
				'order_id' => $transaction['order_id'],
				self::TRANSACTION_ID => $transaction[self::TRANSACTION_ID],
				self::PARENT_TRANSACTION_ID => $transaction[self::PARENT_TRANSACTION_ID],
				'parent_transaction_href' => $this->url->link(self::PG_TRANSACTION, 'user_token=' . $this->session->data[self::USER_TOKEN] . '&id=' . $transaction[self::PARENT_TRANSACTION_ID], true),
				'action' => $transaction['transaction_type'],
				'payment_method' => $title,
				'transaction_state' => $transaction['transaction_state'],
				'amount' => $transaction['amount'],
				'currency' => $transaction['currency'],
				'href' => $this->url->link(self::PG_TRANSACTION, 'user_token=' . $this->session->data[self::USER_TOKEN] . '&id=' . $transaction[self::TRANSACTION_ID], true)
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
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data[self::USER_TOKEN], true)
		);

		$breadcrumbs[] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data[self::USER_TOKEN] . '&type=module', true)
		);

		$breadcrumbs[] = array(
			'text' => $this->language->get(self::HEADING_TITLE),
			'href' => $this->url->link('extension/module/wirecard_pg', 'user_token=' . $this->session->data[self::USER_TOKEN], true)
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
		$data[self::USER_TOKEN] = $this->session->data[self::USER_TOKEN];

		$data['live_chat'] = $this->load->view('extension/wirecard_pg/live_chat', $data);

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		return $data;
	}
}
