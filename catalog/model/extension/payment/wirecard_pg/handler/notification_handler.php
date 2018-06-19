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

class NotificationHandler {

	/**
	 * @param \Wirecard\PaymentSdk\Config\Config $config
	 * @param PGLogger $logger
	 * @param string $payload
	 * @return bool|\Wirecard\PaymentSdk\Response\FailureResponse|\Wirecard\PaymentSdk\Response\InteractionResponse|\Wirecard\PaymentSdk\Response\Response|\Wirecard\PaymentSdk\Response\SuccessResponse
	 */
	public function handleNotification($config, $logger, $payload) {
		try {
			$transactionService = new \Wirecard\PaymentSdk\TransactionService($config);
			$response = $transactionService->handleNotification($payload);
		} catch (\InvalidArgumentException $exception) {
			$logger->error($exception->getMessage());
			return false;
		} catch (\Wirecard\PaymentSdk\Exception\MalformedResponseException $exception) {
			$logger->error($exception->getMessage());
			return false;
		}

		// Return the response or log errors if any happen.
		if ($response instanceof \Wirecard\PaymentSdk\Response\SuccessResponse) {
			return $response;
		} else {
			foreach ($response->getStatusCollection()->getIterator() as $item) {
				$logger->error($item->getDescription());
			}

			return false;
		}
	}
}
