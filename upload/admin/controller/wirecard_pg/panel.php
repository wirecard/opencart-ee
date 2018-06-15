<?php
class ControllerWirecardPGPanel extends Controller {

	const ROUTE = 'extension/payment/wirecard_pg';

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
				'href' => $this->url->link('wirecard_pg/panel/transaction', 'user_token=' . $this->session->data['user_token'] . '&id=' . $transaction['tx_id'], true)
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
			'href' => $this->url->link('wirecard_pg/panel', 'user_token=' . $this->session->data['user_token'], true)
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
		$data = false;

		if ($transaction) {
			$data = array(
				'transaction_id' => $transaction['transaction_id'],
				'response' => json_decode($transaction['response'], true)
			);
		}

		return $data;
	}

}
