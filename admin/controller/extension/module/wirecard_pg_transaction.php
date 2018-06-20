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

require_once __DIR__ . '/wirecard_pg.php';
require_once __DIR__ . '/../payment/wirecard_pg/transaction_handler.php';

/**
 * Class ControllerExtensionPaymentWirecardPGTransaction
 *
 * Transaction controller
 *
 * @since 1.0.0
 */
class ControllerExtensionModuleWirecardPGTransaction extends Controller {

	const ROUTE = 'extension/payment/wirecard_pg';
	const PANEL = 'extension/module/wirecard_pg';
	const TRANSACTION = 'extension/module/wirecard_pg_transaction';

	/**
	 * Display transaction details
	 *
	 * @since 1.0.0
	 */
	public function index() {
		$this->load->language(self::ROUTE);
		$panel = new ControllerExtensionModuleWirecardPG($this->registry);

		$data['title'] = $this->language->get('heading_transaction_details');

		$this->document->setTitle($data['title']);

		$data['breadcrumbs'] = $panel->getBreadcrumbs();

		$data = array_merge($data, $panel->getCommons());

		$data['text_transaction'] = $this->language->get('text_transaction');
		$data['text_response_data'] = $this->language->get('text_response_data');
		$data['text_backend_operations'] = $this->language->get('text_backend_operations');
		$data['route_href'] = $this->url->link(self::TRANSACTION . '/');

		if (isset($this->session->data['admin_error'])) {
			$data['error_warning'] = $this->session->data['admin_error'];
			unset($this->session->data['admin_error']);
		}
		if (isset($this->request->get['id'])) {
			$data['transaction'] = $this->getTransactionDetails($this->request->get['id']);
		} else {
			$data['error_warning'] = $this->language->get('error_no_transaction');
		}

		$this->response->setOutput($this->load->view('extension/wirecard_pg/details', $data));
	}

	/**
	 * Get transaction detail data via id
	 *
	 * @param string $id
	 * @return bool|array
	 * @since 1.0.0
	 */
	public function getTransactionDetails($id) {
		$this->load->model(self::ROUTE);
		$transaction = $this->model_extension_payment_wirecard_pg->getTransaction($id);
		$data = false;

		if ($transaction) {
			$operations = $this->getBackendOperations($transaction);
			$data = array(
				'transaction_id' => $transaction['transaction_id'],
				'response' => json_decode($transaction['response'], true),
				'operations' => ($transaction['transaction_state'] == 'success') ? $operations : false
			);
		}

		return $data;
	}

	/**
	 * Handle cancel transactions
	 *
	 * @since 1.0.0
	 */
	public function cancel() {
		$this->load->language(self::ROUTE);
		$panel = new ControllerExtensionModuleWirecardPG($this->registry);

		$data['title'] = $this->language->get('heading_transaction_details');

		$this->document->setTitle($data['title']);

		$data['breadcrumbs'] = $panel->getBreadcrumbs();

		$data = array_merge($data, $panel->getCommons());

		$transactionHandler = new ControllerExtensionPaymentWirecardPGTransactionHandler($this->registry);

		if (isset($this->request->get['id'])) {
			$this->load->model(self::ROUTE);
			$transaction = $this->model_extension_payment_wirecard_pg->getTransaction($this->request->get['id']);

			$controller = $this->getPaymentController($transaction['payment_method']);
			$transactionId = $transactionHandler->createCancelTransaction($controller, $transaction, $this->config);
			if ($transactionId) {
				$this->response->redirect($this->url->link(self::TRANSACTION, 'user_token=' . $this->session->data['user_token'] . '&id=' . $transactionId, true));
			} else {
				$data['error_warning'] = $this->session->data['admin_error'];
				$this->response->redirect($this->url->link(self::TRANSACTION, 'user_token=' . $this->session->data['user_token'] . '&id=' . $this->request->get['id'], true));
			}
		}

		$this->session->data['admin_error'] = $this->language->get('error_no_transaction');
		$this->response->redirect($this->url->link(self::TRANSACTION, 'user_token=' . $this->session->data['user_token'] . '&id=' . $this->request->get['id'], true));
	}

	/**
	 * Get frontend payment controller
	 *
	 * @param string $methodName
	 * @return ControllerExtensionPaymentGateway|null
	 * @since 1.0.0
	 */
	public function getPaymentController($methodName) {
		$files = glob(
			DIR_CATALOG . 'controller/extension/payment/wirecard_pg_*.php',
			GLOB_BRACE
		);

		foreach ($files as $file) {
			if (is_file($file) && strpos($file, $methodName)) {
				//load catalog controller
				require_once($file);
				$classes = get_declared_classes();
				$class = end($classes);
				/** @var ControllerExtensionPaymentGateway $controller */
				$controller = new $class($this->registry);

				return $controller;

			}
		}

		return null;
	}

	/**
	 * Retrieve backend operations for specific transaction
	 *
	 * @param array $parentTransaction
	 * @return array|bool
	 * @since 1.0.0
	 */
	private function getBackendOperations($parentTransaction) {
		$controller = $this->getPaymentController($parentTransaction['payment_method']);

		/** @var \Wirecard\PaymentSdk\Transaction\Transaction $transaction */
		$transaction = $controller->getTransactionInstance();
		$transaction->setParentTransactionId($parentTransaction['transaction_id']);

		$backendService = new \Wirecard\PaymentSdk\BackendService($controller->getConfig());
		$backOperations = $backendService->retrieveBackendOperations($transaction, true);

		if (!empty($backOperations)) {
			$operations = array();
			foreach ($backOperations as $item => $value) {
				$key = key($value);
				$op = array(
					'action' => $this->url->link(self::TRANSACTION . '/' . $key,
						'user_token=' . $this->session->data['user_token'] . '&id=' . $parentTransaction['transaction_id'],
						true),
					'text' => $value[$key]
				);
				array_push($operations, $op);
			}

			return $operations;
		}

		return false;
	}
}
