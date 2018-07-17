<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/opencart-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/opencart-ee/blob/master/LICENSE
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
	 * @param ControllerExtensionPaymentGateway $payment_controller
	 * @param array $parent_transaction
	 * @param Config $config
	 * @param \Wirecard\PaymentSdk\Transaction\Operation $operation
	 * @param \Wirecard\PaymentSdk\Entity\Amount $amount
	 * @return string
	 * @since 1.0.0
	 */
	public function processTransaction($payment_controller, $parent_transaction, $config, $operation, $amount) {
		$logger = new PGLogger($config);

		// This allows us to return the proper config/transactions if there are different types depending on the operation
		// E.g. Sofort uses a SofortTransaction to debit and a SepaTransaction to credit a bank account.
		$payment_controller->setOperation($operation);

		$backend_service = new \Wirecard\PaymentSdk\BackendService($payment_controller->getConfig(), $logger);
		$transaction = $payment_controller->createTransaction($parent_transaction, $amount);
		try {
			/* @var \Wirecard\PaymentSdk\Response\Response $response */
			$response = $backend_service->process($transaction, $operation);
		} catch ( \Exception $exception ) {
			$logger->error(__METHOD__ . ': ' . get_class($exception) . ' ' . $exception->getMessage());
		}

		if ($response instanceof \Wirecard\PaymentSdk\Response\SuccessResponse) {
			$response_data = $response->getData();
			$order = array(
				'orderId' => $response->getCustomFields()->get('orderId'),
				'amount' => $response_data['requested-amount'],
				'currency_code' => $response_data['currency']
			);

			$this->load->model('extension/payment/wirecard_pg');
			$this->model_extension_payment_wirecard_pg->createTransaction(
				$response,
				$order,
				'awaiting',
				$payment_controller
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
