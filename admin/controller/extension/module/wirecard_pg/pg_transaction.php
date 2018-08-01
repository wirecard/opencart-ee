<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/opencart-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/opencart-ee/blob/master/LICENSE
 */

require_once __DIR__ . '/../wirecard_pg.php';
require_once __DIR__ . '/../../payment/wirecard_pg/transaction_handler.php';

use Wirecard\PaymentSdk\Transaction\Operation;

/**
 * Class ControllerExtensionModuleWirecardPGPGTransaction
 *
 * Transaction controller
 *
 * @since 1.0.0
 */
class ControllerExtensionModuleWirecardPGPGTransaction extends Controller {

	const ROUTE = 'extension/payment/wirecard_pg';
	const PANEL = 'extension/module/wirecard_pg';
	const TRANSACTION = 'extension/module/wirecard_pg/pg_transaction';

	/**
	 * Display transaction details
	 *
	 * @since 1.0.0
	 */
	public function index() {
		$basicInfo = new ExtensionModuleWirecardPGPluginData();
		$this->load->language(self::ROUTE);
		$panel = new ControllerExtensionModuleWirecardPG($this->registry);

		$data['title'] = $this->language->get('heading_transaction_details');

		$this->document->setTitle($data['title']);

		$data['breadcrumbs'] = $panel->getBreadcrumbs();

		$data = array_merge($data, $panel->getCommons(), $basicInfo->getTemplateData());

		$data['text_transaction'] = $this->language->get('text_transaction');
		$data['text_response_data'] = $this->language->get('text_response_data');
		$data['text_backend_operations'] = $this->language->get('text_backend_operations');
		$data['text_request_amount'] = $this->language->get('text_request_amount');
		$data['route_href'] = $this->url->link(self::TRANSACTION . '/');

		if (isset($this->session->data['wirecard_info']['admin_error'])) {
			$data['error_warning'] = $this->session->data['wirecard_info']['admin_error'];
		}
		if (isset($this->request->get['id'])) {
			$data['transaction'] = $this->getTransactionDetails($this->request->get['id']);
			if ('ratepayinvoice' == $data['transaction']['payment_method']) {
                $this->load->language(self::ROUTE . '_ratepayinvoice');
                $basket = $this->getBasketItems($this->request->get['id']);
                $data['basket'] = $this->updateBasketItemQuantity($this->request->get['id'], $basket);
                $data['ratepayinvoice_details'] = $this->load->view('extension/wirecard_pg/ratepayinvoice_details', $data);
            }
		} else {
			$data['error_warning'] = $this->language->get('error_no_transaction');
		}
		if (isset($this->session->data['wirecard_info']['success_message'])) {
			$data['success_message'] = $this->session->data['wirecard_info']['success_message'];
			$data['child_transaction_id'] = $this->session->data['wirecard_info']['child_transaction_id'];
			$data['child_transaction_href'] = $this->session->data['wirecard_info']['child_transaction_href'];
		}

		if (isset($this->session->data['admin_error'])) {
			$data['error_warning'] = $this->session->data['admin_error'];
		}

		unset(
			$this->session->data['wirecard_info'],
			$this->session->data['admin_error']
		);

		$this->response->setOutput($this->load->view('extension/wirecard_pg/details', $data));
	}

	/**
	 * Get transaction detail data via id
	 *
	 * @param string $transaction_id
	 * @return bool|array
	 * @since 1.0.0
	 */
	public function getTransactionDetails($transaction_id) {
		$this->load->model(self::ROUTE);
		$this->load->language(self::ROUTE);

		$transaction = $this->model_extension_payment_wirecard_pg->getTransaction($transaction_id);
		$data = false;

		if ($transaction) {
			$operations = $this->getBackendOperations($transaction);
			$amount = $this->model_extension_payment_wirecard_pg->getTransactionMaxAmount($transaction_id);
			$data = array(
				'transaction_id' => $transaction['transaction_id'],
				'response' => json_decode($transaction['response'], true),
				'amount' => $amount,
				'currency' => $transaction['currency'],
				'operations' => ($transaction['transaction_state'] == 'success') ? $operations : false,
				'payment_method' => $transaction['payment_method'],
				'action' => $this->url->link(
					self::TRANSACTION . '/process', 'user_token=' . $this->session->data['user_token'] . '&id=' . $transaction['transaction_id'],
					true
				)
			);
		}

		return $data;
	}

	/**
	 * Handle back-end transactions
	 *
	 * @since 1.0.0
	 */
	public function process() {
		$this->load->language(self::ROUTE);
		$panel = new ControllerExtensionModuleWirecardPG($this->registry);

		$data['title'] = $this->language->get('heading_transaction_details');

		$this->document->setTitle($data['title']);

		$data['breadcrumbs'] = $panel->getBreadcrumbs();

		$data = array_merge($data, $panel->getCommons());

		$transaction_handler = new ControllerExtensionPaymentWirecardPGTransactionHandler($this->registry);

		if (isset($this->request->get['id']) && isset($this->request->post['operation'])) {
			$this->load->model(self::ROUTE);
			$transaction = $this->model_extension_payment_wirecard_pg->getTransaction($this->request->get['id']);
			$operation = $this->request->post['operation'];
			$payment_method = $this->request->post['payment-method'];
			if ('ratepayinvoice' == $payment_method) {
			    $transaction['basket'] = $this->addResponseBasket($transaction);
            }
            $amount = new \Wirecard\PaymentSdk\Entity\Amount($this->request->post['amount'], $this->request->post['currency']);
            if ('cancel' == $operation) {
			    $amount = null;
            }

			$controller = $this->getPaymentController($transaction['payment_method']);
			$transaction_id = $transaction_handler->processTransaction($controller, $transaction, $this->config, $operation, $amount);
			if ($transaction_id) {
				$this->session->data['wirecard_info']['success_message'] = $this->language->get('success_new_transaction');
				$this->session->data['wirecard_info']['child_transaction_id'] = $transaction_id;
				$this->session->data['wirecard_info']['child_transaction_href'] = $this->url->link(self::TRANSACTION, 'user_token=' . $this->session->data['user_token'] . '&id=' . $transaction_id, true);
				$this->response->redirect($this->url->link(self::TRANSACTION, 'user_token=' . $this->session->data['user_token'] . '&id=' . $this->request->get['id'], true));
			} else {
				$this->response->redirect($this->url->link(self::TRANSACTION, 'user_token=' . $this->session->data['user_token'] . '&id=' . $this->request->get['id'], true));
			}
		}

		$this->session->data['wirecard_info']['admin_error'] = $this->language->get('error_no_transaction');
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

		$backend_service = new \Wirecard\PaymentSdk\BackendService($controller->getConfig());
		$backend_operations = $backend_service->retrieveBackendOperations($transaction, true);

		if (!empty($backend_operations)) {
			$operations = array();
			foreach ($backend_operations as $key => $value) {
				if (Operation::CREDIT == $key && !$this->config->get('payment_wirecard_pg_sepacredit_status')) {
					continue;
				}

				$op = array(
					'action' => $key,
					'text' => $this->language->get($key),
				);

				array_push($operations, $op);
			}

			return $operations;
		}

		return false;
	}

    /**
     * Create multidimensional basket array from response
     *
     * @param $transaction_id
     * @return array|bool
     * @since 1.1.0
     */
    private function getBasketItems($transaction_id) {
        $this->load->model(self::ROUTE);
        $this->load->language(self::ROUTE);

        $transaction = $this->model_extension_payment_wirecard_pg->getTransaction($transaction_id);

        $basket = array();
        if ($transaction) {
            foreach (json_decode($transaction['response']) as $key => $value) {
                if (strpos($key, 'order-items') !== false) {
                    $item_start = substr($key, strpos($key, 'order-item.') + strlen('order-item.'));
                    $item_count = substr($item_start, 0, strpos($item_start, '.'));
                    $item_name = substr($item_start, strpos($item_start, '.') + 1);
                    $item_name = str_replace('-', '_', $item_name);
                    $basket[$item_count][$item_name] = $value;
                }
            }
            return $basket;
        }
        return false;
    }

    /**
     * Update response quantities to actual basket quantity
     *
     * @param string $transaction_id
     * @param array $basket
     * @return mixed
     * @since 1.1.0
     */
    private function updateBasketItemQuantity($transaction_id, $basket) {
        $this->load->model(self::ROUTE);
        $this->load->language(self::ROUTE);
        $parent_quantities = $this->getArticleNumbersWithQuantity($basket);

        $transactions = $this->model_extension_payment_wirecard_pg->getChildTransactions($transaction_id);
        if ($transactions) {
            foreach ($transactions as $transaction) {
                $child_basket = $this->getBasketItems($transaction['transaction_id']);
                $child_quantities = $this->getArticleNumbersWithQuantity($child_basket);
                foreach ($child_quantities as $key => $value) {
                    $parent_quantities[$key] = $parent_quantities[$key] - $value;
                }
            }
        }
        foreach ($basket as $key => $item) {
            if (isset($parent_quantities[$item['article_number']])) {
                $basket[$key]['quantity'] = $parent_quantities[$item['article_number']];
            }
        }
        return $basket;
    }

    /**
     * Get quantities for specific articlenumbers
     *
     * @param array $basket
     * @return array
     * @since 1.1.0
     */
    private function getArticleNumbersWithQuantity($basket) {
        $quantities = array();
        foreach ($basket as $key => $value) {
            $quantities[$value['article_number']] = $value['quantity'];
        }
        return $quantities;
    }

    /**
     * Add updated basket item quantities
     *
     * @param Wirecard\PaymentSdk\Transaction\Transaction $transaction
     * @return array|bool
     * @since 1.1.0
     */
	private function addResponseBasket($transaction) {
        $basket = $this->getBasketItems($transaction['transaction_id']);
        foreach ($basket as $key => $value) {
            if (isset($this->request->post['quantity-' . $key])) {
                $basket[$key]['quantity'] = $this->request->post['quantity-' . $key];
            }
        }
        return $basket;
    }
}
