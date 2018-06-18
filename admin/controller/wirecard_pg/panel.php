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
 * Class ControllerWirecardPGPanel
 *
 * Basic payment extension controller
 *
 * @since 1.0.0
 */
class ControllerWirecardPGPanel extends Controller {

	const ROUTE = 'extension/payment/wirecard_pg';
	const PANEL = 'wirecard_pg/panel';

	/**
	 * Basic index method
	 *
	 * @since 1.0.0
	 */
	public function index() {
		$this->load->language(self::ROUTE);

		$this->document->setTitle($this->language->get('heading_title'));

		$data['breadcrumbs'] = $this->getBreadcrumbs();

		$data = array_merge($data, $this->getCommons());

		$data['transactions'] = $this->loadTransactionData();

		$this->response->setOutput($this->load->view('extension/wirecard_pg/panel', $data));
	}

	/**
	 * Install process
	 *
	 * @since 1.0.0
	 */
	public function install() {
		$this->load->model('user/user_group');

		$this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'wirecard_pg/panel');
		$this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', 'wirecard_pg/panel');

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
			$transactions[] = array(
				'tx_id' => $transaction['tx_id'],
				'order_id' => $transaction['order_id'],
				'transaction_id' => $transaction['transaction_id'],
				'parent_transaction_id' => $transaction['parent_transaction_id'],
				'action' => $transaction['transaction_type'],
				'payment_method' => $transaction['payment_method'],
				'transaction_state' => $transaction['transaction_state'],
				'amount' => $transaction['amount'],
				'currency' => $transaction['currency'],
				'href' => $this->url->link(self::PANEL . '/transaction', 'user_token=' . $this->session->data['user_token'] . '&id=' . $transaction['tx_id'], true)
			);
		}

		return $transactions;
	}

	/**
	 * Display transaction details
	 *
	 * @since 1.0.0
	 */
	public function transaction() {
		$this->load->language(self::ROUTE);

		$data['title'] = $this->language->get('heading_transaction_details');

		$this->document->setTitle($data['title']);

		$data['breadcrumbs'] = $this->getBreadcrumbs();

		$data = array_merge($data, $this->getCommons());

		$data['text_transaction'] = $this->language->get('text_transaction');
		$data['text_response_data'] = $this->language->get('text_response_data');
		$data['text_backend_operations'] = $this->language->get('text_backend_operations');

		if (isset($this->request->get['id'])) {
			$data['transaction'] = $this->getTransactionDetails($this->request->get['id']);
		} else {
			$data['error'] = $this->language->get('error_no_transaction');
		}

		$this->response->setOutput($this->load->view('extension/wirecard_pg/details', $data));
	}

	/**
	 * Get breadcrumb data
	 *
	 * @return array
	 * @since 1.0.0
	 */
	private function getBreadcrumbs() {
		$breadcrumbs = array();

		$breadcrumbs[] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$breadcrumbs[] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link(self::PANEL, 'user_token=' . $this->session->data['user_token'], true)
		);

		return $breadcrumbs;
	}

	/**
	 * Get common header, sidebar and footer
	 *
	 * @return array
	 * @since 1.0.0
	 */
	private function getCommons() {
		$data['user_token'] = $this->session->data['user_token'];

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		return $data;
	}

	/**
	 * Get transaction detail data via id
	 *
	 * @param $id
	 * @return bool|array
	 * @since 1.0.0
	 */
	private function getTransactionDetails($id) {
		$this->load->model(self::ROUTE);
		$transaction = $this->model_extension_payment_wirecard_pg->getTransaction($id);
		$operations = $this->getBackendOperations($transaction);
		$data = false;

		if ($transaction) {
			$data = array(
				'transaction_id' => $transaction['transaction_id'],
				'response' => json_decode($transaction['response'], true),
				'operations' => $operations
			);
		}

		return $data;
	}

	/**
	 * Retrieve backend operations for specific transaction
	 *
	 * @param array $childTransaction
	 * @return array|bool
	 * @since 1.0.0
	 */
	private function getBackendOperations($childTransaction) {
		$files = glob(
			DIR_CATALOG . 'controller/extension/payment/wirecard_pg_*.php',
			GLOB_BRACE
		);

		/** @var ControllerExtensionPaymentGateway $controller */
		foreach ($files as $file) {
			if (is_file($file) && strpos($file, $childTransaction['payment_method'])) {
				//load catalog controller
				require_once($file);
				$controller = new ControllerExtensionPaymentWirecardPGPayPal($this->registry);
					/** @var \Wirecard\PaymentSdk\Transaction\Transaction $transaction */
					$transaction = $controller->getTransactionInstance();
					$transaction->setParentTransactionId($childTransaction['transaction_id']);

					$backendService = new \Wirecard\PaymentSdk\BackendService($controller->getConfig());
					$backOperations = $backendService->retrieveBackendOperations($transaction, true);

					$operations = array();
					foreach ($backOperations as $operation) {
						$operations = array_merge($operations, $operation);
					}

					return $operations;
			}
		}

		return false;
	}
}
