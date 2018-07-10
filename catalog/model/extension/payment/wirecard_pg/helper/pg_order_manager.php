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
 * Class PGOrderManager
 *
 * @since 1.0.0
 */
class PGOrderManager extends Model {

	const PENDING = 1;
	const PROCESSING = 2;
	const CHECK_PAYER_RESPONSE = 'check-payer-response';

	private $order_states = array(
		'Authorized' => 'authorized',
		'Processing' => 'processing',
		'Canceled' => 'cancelled',
		'Refunded' => 'refunded',
		'Failed' => 'failed'
	);

	/**
	 * Create new order with specific orderstate
	 *
	 * @param \Wirecard\PaymentSdk\Response\Response $response
	 * @param ControllerExtensionPaymentGateway $payment_controller
	 * @since 1.0.0
	 */
	public function createResponseOrder($response, $payment_controller) {
		$this->load->model('checkout/order');
		$order_id = $response->getCustomFields()->get('orderId');
		$order = $this->model_checkout_order->getOrder($order_id);
		/** @var ModelExtensionPaymentGateway $transaction_model */
		$transaction_model = $payment_controller->getModel();

		if (!is_array($transaction_model->getTransaction($response->getTransactionId()))) {
			$this->model_checkout_order->addOrderHistory(
				$order_id,
				self::PENDING,
				'<pre>' . htmlentities($response->getRawData()) . '</pre>',
				false
			);
			$transaction_model->createTransaction($response, $order, 'awaiting', $payment_controller);
		}
	}

	/**
	 * Create new order with specific orderstate
	 *
	 * @param \Wirecard\PaymentSdk\Response\Response $response
	 * @param ControllerExtensionPaymentGateway $payment_controller
	 * @since 1.0.0
	 */
	public function createNotifyOrder($response, $payment_controller) {
		//credit card special case for 3d transactions
		if (self::CHECK_PAYER_RESPONSE == $response->getTransactionType()) {
			return;
		}
		$order_id = $response->getCustomFields()->get('orderId');
		$this->load->model('checkout/order');
		$this->load->language('extension/payment/wirecard_pg');
		$order = $this->model_checkout_order->getOrder($order_id);
		/** @var ModelExtensionPaymentGateway $transaction_model */
		$transaction_model = $payment_controller->getModel();

		$logger = $payment_controller->getLogger();
		$backend_service = new \Wirecard\PaymentSdk\BackendService($payment_controller->getConfig(), $logger);
		$state = $this->getOrderState($backend_service->getOrderState($response->getTransactionType()));
		if (self::PENDING == $order['order_status_id'] || 0 == $order['order_status_id']) {
			//Send notification mail -without- comments
			$this->model_checkout_order->addOrderHistory(
				$order_id,
				$state,
				'',
				true
			);
			//Update order history with comments and do -not- send confirmation for customer
			$this->model_checkout_order->addOrderHistory(
				$order_id,
				$state,
				'<pre>' . htmlentities($response->getRawData()) . '</pre>',
				false
			);
			if ($response instanceof \Wirecard\PaymentSdk\Response\SuccessResponse && $transaction_model->getTransaction($response->getTransactionId())) {
				$transaction_model->updateTransactionState($response, 'success');
			} else {
				$transaction_model->createTransaction($response, $order, 'success', $payment_controller);
			}
		} else {
			if ($response instanceof \Wirecard\PaymentSdk\Response\SuccessResponse) {
				$this->updateNotifyOrder($response, $transaction_model, $payment_controller);
			}
		}
	}

	/**
	 * Update order state and transaction table
	 *
	 * @param \Wirecard\PaymentSdk\Response\SuccessResponse $response
	 * @param ModelExtensionPaymentGateway $transaction_model
	 * @param ControllerExtensionPaymentGateway $payment_controller
	 * @since 1.0.0
	 */
	public function updateNotifyOrder($response, $transaction_model, $payment_controller) {
		$logger = $payment_controller->getLogger();
		$backend_service = new \Wirecard\PaymentSdk\BackendService($payment_controller->getConfig(), $logger);
		$state = $this->getOrderState($backend_service->getOrderState($response->getTransactionType()));
		//Send notification mail -without- comments
		$this->model_checkout_order->addOrderHistory(
			$response->getCustomFields()->get('orderId'),
			$state,
			'',
			true
		);
		//Update order history with comments and do -not- send confirmation for customer
		$this->model_checkout_order->addOrderHistory(
			$response->getCustomFields()->get('orderId'),
			$state,
			'<pre>' . htmlentities($response->getRawData()) . '</pre>',
			false
		);

		if ($backend_service->isFinal($response->getTransactionType())) {
			$transaction_model->updateTransactionState($response, 'closed');
		} else {
			$transaction_model->updateTransactionState($response, 'success');
		}
	}

	/**
	 * Update/Delete order history after cancel or failure
	 *
	 * @param int $order_id
	 * @param string $state
	 * @param int $delete
	 * @since 1.0.0
	 */
	public function updateCancelFailureOrder($order_id, $state, $delete) {
		$this->load->model('checkout/order');

		if ($delete) {
			$this->model_checkout_order->deleteOrder($order_id);
		} else {
			$this->model_checkout_order->addOrderHistory(
				$order_id,
				$this->getOrderState($state),
				'',
				false
			);
		}
	}

	/**
	 * Get Order state per transaction type
	 *
	 * @param string $state
	 * @return int
	 * @since 1.0.0
	 */
	public function getOrderState($state) {
		$this->load->model('localisation/order_status');

		$order_status = $this->model_localisation_order_status->getOrderStatuses();
		foreach ($order_status as $status) {
			if (isset($this->order_states[$status['name']]) && ($state == $this->order_states[$status['name']])) {
				return $status['order_status_id'];
			}
		}
	}
}
