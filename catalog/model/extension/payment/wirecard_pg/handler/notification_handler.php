<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/opencart-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/opencart-ee/blob/master/LICENSE
 */

class NotificationHandler {

	/**
	 * @param \Wirecard\PaymentSdk\Config\Config $config
	 * @param PGLogger $logger
	 * @param string $payload
	 * @return bool|\Wirecard\PaymentSdk\Response\FailureResponse|\Wirecard\PaymentSdk\Response\InteractionResponse|\Wirecard\PaymentSdk\Response\Response|\Wirecard\PaymentSdk\Response\SuccessResponse
	 */
	public function handleNotification($config, $logger, $payload) {
		try {
			$transaction_service = new \Wirecard\PaymentSdk\TransactionService($config, $logger);
			$response = $transaction_service->handleNotification($payload);
		} catch (\InvalidArgumentException $exception) {
			$logger->error($exception->getMessage());
			return false;
		} catch (\Wirecard\PaymentSdk\Exception\MalformedResponseException $exception) {
			$logger->error($exception->getMessage());
			return false;
		}

		// Return the response or log errors if any happen.
		if ($response instanceof \Wirecard\PaymentSdk\Response\SuccessResponse) {
			$logger->debug('Notify Response: ' . $response->getRawData());
			return $response;
		} else {
			foreach ($response->getStatusCollection()->getIterator() as $item) {
				$logger->error($item->getDescription());
			}

			return false;
		}
	}
}
