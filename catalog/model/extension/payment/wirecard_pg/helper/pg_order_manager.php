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

	/**
	 * Create new order with specific orderstate
	 *
	 * @param \Wirecard\PaymentSdk\Response\Response $response
	 * @since 1.0.0
	 */
	public function createResponseOrder($response) {
		$this->load->model('checkout/order');
		$orderId = $response->getCustomFields()->get('orderId');
		$order = $this->model_checkout_order->getOrder($orderId);

		//Update pending order with responsedata
		if ($order['order_status'] == 1) {
			$this->model_checkout_order->addOrderHistory($orderId, 1, json_encode($response->getData()), false);
			//transaction should be saved here
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
		//Example for right usage of order states and transaction types
		$orderId = $response->getCustomFields()->get('orderId');
		$this->load->model('checkout/order');
		$this->load->language('extension/payment/wirecard_pg');
		$order = $this->model_checkout_order->getOrder($orderId);

		$backendService = new \Wirecard\PaymentSdk\BackendService($paymentController->getConfig());
		if ($order['order_status_id']) {
			if ($order['order_status_id'] == 1) {
				$this->model_checkout_order->addOrderHistory(
					$orderId,
					//update the order state
					2/*$this->getOrderState($backendService->getOrderState($response->getTransactionType()))*/,
					'<pre>' . htmlentities($response->getRawData()) . '</pre>',
					true
				);
			}
		}
	}
}
