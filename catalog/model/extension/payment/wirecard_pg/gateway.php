<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/opencart-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/opencart-ee/blob/master/LICENSE
 */

/**
 * Class ModelExtensionPaymentGateway
 *
 * @since 1.0.0
 */
abstract class ModelExtensionPaymentGateway extends Model {

	/**
	 * @var string
	 * @since 1.0.0
	 */
	protected $prefix = 'payment_wirecard_pg_';

	/**
	 * @var string
	 * @since 1.0.0
	 */
	protected $type;

	/**
	 * Get a logger instance
	 *
	 * @return PGLogger
	 * @since 1.0.0
	 */
	protected function getLogger() {
		return new PGLogger($this->config);
	}

	/**
	 * Default payment method getter, method should only be returned if activated
	 *
	 * @param $address
	 * @param $total
	 * @return array
	 * @since 1.0.0
	 */
	public function getMethod($address, $total) {
		$prefix = $this->prefix . $this->type;
		$base_url = $this->config->get('config_url');

		$this->load->language('extension/payment/wirecard_pg_' . $this->type);
		$logo = '<img src="' . $base_url . 'image/catalog/wirecard_pg_'. $this->type .'.png" />';
		$code = $this->session->data['language'];
		$code = substr( $code, 0, 2 );
		$title = $logo . ' ' . $this->config->get($prefix . '_title' )['en'];
		if (isset($code) && isset($this->config->get($prefix . '_title' )[$code])) {
			$title = $logo . ' ' . $this->config->get($prefix . '_title' )[$code];
		}

		$method_data = array(
			'code'       => 'wirecard_pg_' . $this->type,
			'title'      => $title,
			'terms'      => '',
			'sort_order' => $this->config->get($prefix . '_sort_order')
		);

		return $method_data;
	}

	/**
	 * Process transaction request
	 *
	 * @param $config
	 * @param $transaction
	 * @param string $payment_action
	 * @return \Wirecard\PaymentSdk\Response\Response
	 * @throws Exception
	 * @since 1.0.0
	 */
	public function sendRequest($config, $transaction, $payment_action) {
		$this->load->language('extension/payment/wirecard_pg');

		$logger = $this->getLogger();
		$transaction_service = new \Wirecard\PaymentSdk\TransactionService($config, $logger);

		$redirect = $this->url->link('checkout/checkout', '', true);

		try {
			/* @var \Wirecard\PaymentSdk\Response\Response $response */
			$response = $transaction_service->process($transaction, $payment_action);
		} catch (Exception $exception) {
			$logger->error(get_class($exception) . ' ' . $exception->getMessage());
			$this->session->data['error'] = $this->language->get('order_error');

			$redirect = $this->url->link('checkout/checkout', '', true);

			return $redirect;
		}

		if ($response instanceof \Wirecard\PaymentSdk\Response\InteractionResponse) {
			$redirect = $response->getRedirectUrl();
		} elseif ($response instanceof \Wirecard\PaymentSdk\Response\FormInteractionResponse) {
			$form_fields = $response
				->getFormFields()
				->getIterator()
				->getArrayCopy();

			if (!array_key_exists('sync_response', $form_fields)) {
				return $this->handleFormInteractionPostRequest($response);
			}

			$query = http_build_query($form_fields);
			$redirect = $response->getUrl() . '&' . $query;
		} elseif ($response instanceof \Wirecard\PaymentSdk\Response\FailureResponse) {
			$errors = '';

			foreach ($response->getStatusCollection()->getIterator() as $item) {
				$errors .= $item->getDescription() . "<br>\n";
				$logger->error($item->getDescription());
			}

			$this->session->data['error'] = $errors;
			$redirect = $this->url->link('checkout/checkout', '', true);
		} else {
			$this->session->data['error'] = $this->language->get('order_error');
		}

		return $redirect;
	}

	/**
	 * Create transaction entry
	 *
	 * @param \Wirecard\PaymentSdk\Response\SuccessResponse $response
	 * @param array $order
	 * @param string $transaction_state
	 * @param ControllerExtensionPaymentGateway $payment_controller
	 * @since 1.0.0
	 */
	public function createTransaction($response, $order, $transaction_state, $payment_controller) {
		$amount = $response->getData()['requested-amount'];
		$order_id = $response->getCustomFields()->get('orderId');
		$currency = $order['currency_code'];

		$this->db->query("
            INSERT INTO `" . DB_PREFIX . "wirecard_ee_transactions` SET 
            `order_id` = '" . (int)$order_id . "', 
            `transaction_id` = '" . $this->db->escape($response->getTransactionId()) . "', 
            `parent_transaction_id` = '', 
            `transaction_type` = '" . $this->db->escape($response->getTransactionType()) . "',
            `payment_method` = '" . $this->db->escape($payment_controller->getType()) . "', 
            `transaction_state` = '" . $this->db->escape($transaction_state) . "',
            `amount` = '" . (float)$amount . "',
            `currency` = '" . $this->db->escape($currency) . "',
            `response` = '" . $this->db->escape(json_encode($response->getData())) . "',
            `xml` = '" . $this->db->escape($response->getRawData()) . "',
            `date_added` = NOW()
            ");
	}

	/**
	 * Update transaction with specific transactionstate
	 *
	 * @param \Wirecard\PaymentSdk\Response\SuccessResponse $response
	 * @param $transaction_state
	 * @since 1.0.0
	 */
	public function updateTransactionState($response, $transaction_state) {
		$this->db->query("
        UPDATE `" . DB_PREFIX . "wirecard_ee_transactions` SET 
            `transaction_state` = '" . $this->db->escape($transaction_state) . "', 
            `response` = '" . $this->db->escape(json_encode($response->getData())) . "',
            `xml` = '" . $this->db->escape($response->getRawData()) . "',
            `transaction_type` = '" . $this->db->escape($response->getTransactionType()) . "',
            `date_modified` = NOW() WHERE 
            `transaction_id` = '" . $this->db->escape($response->getTransactionId()) . "'
        ");
	}

	/**
	 * Get transaction via transaction id
	 *
	 * @param $transaction_id
	 * @return bool|array
	 * @since 1.0.0
	 */
	public function getTransaction($transaction_id) {
		$query = $this->db->query("
	        SELECT * FROM `" . DB_PREFIX . "wirecard_ee_transactions` WHERE `transaction_id` = '" . $this->db->escape($transaction_id) . "'
	    ");

		if ($query->num_rows) {
			return $query->row;
		}

		return false;
	}

	/**
	 * Get common blocks for building a template
	 *
	 * @return array
	 * @since 1.1.0
	 */
	public function getCommonBlocks() {
		$data = [
			'continue' => $this->url->link('common/home'),
			'column_left' => $this->load->controller('common/column_left'),
			'column_right' => $this->load->controller('common/column_right'),
			'content_top' => $this->load->controller('common/content_top'),
			'content_bottom' => $this->load->controller('common/content_bottom'),
			'footer' => $this->load->controller('common/footer'),
			'header' => $this->load->controller('common/header'),
		];

		if ($this->customer->isLogged()) {
			$data['text_message'] = sprintf(
				$this->language->get('text_customer'),
				$this->url->link('account/account', '', true),
				$this->url->link('account/order', '', true),
				$this->url->link('account/download', '', true),
				$this->url->link('information/contact')
			);
		} else {
			$data['text_message'] = sprintf(
				$this->language->get('text_guest'),
				$this->url->link('information/contact')
			);
		}

		return $data;
	}

	/**
	 * Handles the redirection of a customer via a submitted form.
	 *
	 * @param \Wirecard\PaymentSdk\Response\FormInteractionResponse $response
	 * @return mixed
	 * @since 1.1.0
	 */
	public function handleFormInteractionPostRequest($response) {
		$this->load->language('information/static');
		$this->load->language('language/extension/wirecard_pg');

		$data = [
			'url' => $response->getUrl(),
			'method' => $response->getMethod(),
			'form_fields' => $response->getFormFields(),
			'redirect_text' => $this->language->get('redirect_text'),
		];

		$data = array_merge($this->getCommonBlocks(), $data);
		return $this->load->view('extension/payment/wirecard_interaction_response', $data);
	}
}
