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

include_once(DIR_SYSTEM . 'library/autoload.php');

/**
 * Class ControllerExtensionPaymentWirecardPGTransactionHandler
 *
 * Transactionhandler controller
 *
 * @since 1.0.0
 */
class ControllerExtensionPaymentWirecardPGTransactionHandler extends Controller {

	/**
	 * Send request with specific transaction and operation
	 *
	 * @param ControllerExtensionPaymentGateway $paymentController
	 * @param array $parentTransaction
	 * @param Config $config
	 * @param \Wirecard\PaymentSdk\Transaction\Operation $operation
	 * @param \Wirecard\PaymentSdk\Entity\Amount $amount
	 * @return string
	 * @since 1.0.0
	 */
	public function processTransaction($paymentController, $parentTransaction, $config, $operation, $amount) {
		$logger = new PGLogger($config);
		$backendTransactionService = new \Wirecard\PaymentSdk\BackendService($paymentController->getConfig(), $logger);
		$transaction = $paymentController->createTransaction($parentTransaction, $amount);

		try {
			/* @var \Wirecard\PaymentSdk\Response\Response $response */
			$response = $backendTransactionService->process($transaction, $operation);
		} catch ( \Exception $exception ) {
			$logger->error(__METHOD__ . ':' . $exception->getMessage());
		}

		if ($response instanceof \Wirecard\PaymentSdk\Response\SuccessResponse) {
			$responseData = $response->getData();
			$order = array(
				'orderId' => $response->getCustomFields()->get('orderId'),
				'amount' => $responseData['requested-amount'],
				'currency_code' => $responseData['currency']
			);

			$this->load->model('extension/payment/wirecard_pg');
			$this->model_extension_payment_wirecard_pg->createTransaction(
				$response,
				$order,
				'awaiting',
				$paymentController
			);

			return $response->getTransactionId();
		}

		$this->session->data['admin_error'] = $this->language->get('error_occured');

		if ($response instanceof \Wirecard\PaymentSdk\Response\FailureResponse) {
			$errors = '';

			foreach ($response->getStatusCollection()->getIterator() as $item) {
				$errors .= $item->getDescription() . "<br>\n";
				$logger->error($item->getDescription());
			}

			$this->session->data['admin_error'] = $errors;
		}

		return false;
	}
}
