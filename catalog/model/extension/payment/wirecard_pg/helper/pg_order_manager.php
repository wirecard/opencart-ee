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

	private $orderStates = array(
		'Authorized' => 'authorized',
		'Processing' => 'processing',
		'Canceled' => 'cancelled',
		'Refunded' => 'refunded'
	);

	/**
	 * Create new order with specific orderstate
	 *
	 * @param \Wirecard\PaymentSdk\Response\Response $response
	 * @param ControllerExtensionPaymentGateway $paymentController
	 * @since 1.0.0
	 */
	public function createResponseOrder($response, $paymentController) {
		$this->load->model('checkout/order');
		$orderId = $response->getCustomFields()->get('orderId');
		$order = $this->model_checkout_order->getOrder($orderId);
		/** @var ModelExtensionPaymentGateway $transactionModel */
		$transactionModel = $paymentController->getModel();

		if (self::PROCESSING != $order['order_status_id'] && !is_array($transactionModel->getTransaction($response->getTransactionId()))) {
			$this->model_checkout_order->addOrderHistory(
				$orderId,
				self::PENDING,
				'<pre>' . htmlentities($response->getRawData()) . '</pre>',
				false
			);
			$transactionModel->createTransaction($response, $order, 'awaiting', $paymentController);
		}
	}

	/**
	 * Create new order with specific orderstate
	 *
	 * @param \Wirecard\PaymentSdk\Response\Response $response
	 * @param ControllerExtensionPaymentGateway $paymentController
	 * @since 1.0.0
	 */
	public function createNotifyOrder($response, $paymentController) {
		//credit card special case for 3d transactions
		if (self::CHECK_PAYER_RESPONSE == $response->getTransactionType()) {
			return;
		}
		$orderId = $response->getCustomFields()->get('orderId');
		$this->load->model('checkout/order');
		$this->load->language('extension/payment/wirecard_pg');
		$order = $this->model_checkout_order->getOrder($orderId);
		/** @var ModelExtensionPaymentGateway $transactionModel */
		$transactionModel = $paymentController->getModel();

		$logger = $paymentController->getLogger();
		$backendService = new \Wirecard\PaymentSdk\BackendService($paymentController->getConfig(), $logger);
		$state = $this->getOrderState($backendService->getOrderState($response->getTransactionType()));
		if (self::PENDING == $order['order_status_id'] || 0 == $order['order_status_id']) {
			$this->model_checkout_order->addOrderHistory(
				$orderId,
				$state,
				'<pre>' . htmlentities($response->getRawData()) . '</pre>',
				true
			);
			if ($response instanceof \Wirecard\PaymentSdk\Response\SuccessResponse && $transactionModel->getTransaction($response->getTransactionId())) {
				$transactionModel->updateTransactionState($response, 'success');
			} else {
				$transactionModel->createTransaction($response, $order, 'success', $paymentController);
			}
		} else {
			if ($response instanceof \Wirecard\PaymentSdk\Response\SuccessResponse) {
				$this->updateNotifyOrder($response, $transactionModel, $paymentController);
			}
		}
	}

	/**
	 * Update order state and transaction table
	 *
	 * @param \Wirecard\PaymentSdk\Response\SuccessResponse $response
	 * @param ModelExtensionPaymentGateway $transactionModel
	 * @param ControllerExtensionPaymentGateway $paymentController
	 * @since 1.0.0
	 */
	public function updateNotifyOrder($response, $transactionModel, $paymentController) {
		$logger = $paymentController->getLogger();
		$backendService = new \Wirecard\PaymentSdk\BackendService($paymentController->getConfig(), $logger);
		$state = $this->getOrderState($backendService->getOrderState($response->getTransactionType()));
		$this->model_checkout_order->addOrderHistory(
			$response->getCustomFields()->get('orderId'),
			$state,
			'<pre>' . htmlentities($response->getRawData()) . '</pre>',
			true
		);
		if ($backendService->isFinal($response->getTransactionType())) {
			$transactionModel->updateTransactionState($response, 'closed');
		} else {
			$transactionModel->updateTransactionState($response, 'success');
		}
	}

	/**
	 * Update order history and transaction entry after cancelation
	 *
	 * @param $orderId
	 * @since 1.0.0
	 */
	public function updateCancelOrder($orderId) {
		$this->load->model('checkout/order');
		$this->model_checkout_order->addOrderHistory(
			$orderId,
			$this->getOrderState('cancelled'),
			'',
			false
		);
		//Update transaction in transactiontable here
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

		$orderStatus = $this->model_localisation_order_status->getOrderStatuses();
		foreach ($orderStatus as $status) {
			if (isset($this->orderStates[$status['name']]) && ($state == $this->orderStates[$status['name']])) {
				return $status['order_status_id'];
			}
		}
	}
}
