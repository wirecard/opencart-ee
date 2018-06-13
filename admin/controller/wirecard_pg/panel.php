<?php
class ControllerWirecardPGPanel extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('extension/payment/wirecard_pg');

		$this->document->setTitle($this->language->get('heading_title'));

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('wirecard_pg/panel', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['user_token'] = $this->session->data['user_token'];

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$data['transactions'] = $this->loadTransactionData();

		$this->response->setOutput($this->load->view('wirecard_pg/panel', $data));
	}

	/**
	 * Load transactions per page
	 *
	 * @since 1.0.0
	 */
	public function loadTransactionData() {
		$this->load->model('extension/payment/wirecard_pg');
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
                'currency' => $transaction['currency']
            );
        }

		return $transactions;
	}
}
