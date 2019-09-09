<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/opencart-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/opencart-ee/blob/master/LICENSE
 */

require_once(dirname(__FILE__) . '/pg_account_info.php');
require_once(dirname(__FILE__) . '/pg_risk_info.php');
include_once(DIR_SYSTEM . 'library/autoload.php');

class ThreeDParamService {
	/**
	 * Adds three ds parameters to the passed transaction
	 *
	 * @param ControllerExtensionPaymentGateway $gateway
	 * @param Registry $registry
	 * @param \Wirecard\PaymentSdk\Transaction\Transaction $transaction
	 * @param int|null $vault_token
	 * @param array|null $order
	 *
	 * @since 1.5.0
	 */
	public static function addThreeDsParameters($gateway, $registry, $transaction, $vault_token = null, $order = null) {
		// Account Info
		$account_holder = $transaction->getAccountHolder();

		// If there is no account holder set
		// Create an account holder and shipping and add to the transaction
		if (!$account_holder instanceof \Wirecard\PaymentSdk\Entity\AccountHolder && !empty($order)) {
			$account_holder_helper = new PGAccountHolder();
			$account_holder = $account_holder_helper->createBasicAccountHolder($order, PGAccountHolder::BILLING);
			$account_holder_shipping = $account_holder_helper->createBasicAccountHolder($order, PGAccountHolder::SHIPPING);

			$transaction->setAccountHolder($account_holder);
			$transaction->setShipping($account_holder_shipping);
		}

		// Account Info
		$account_info = new PGAccountInfo($registry, $gateway, $account_holder, $vault_token);
		$account_info->mapAccountInfo();

		// Risk Info
		$risk_info = new PGRiskInfo($registry, $transaction);
		$risk_info->mapRiskInfo();

		// Hardcoded
		$transaction->setIsoTransactionType(\Wirecard\PaymentSdk\Constant\IsoTransactionType::GOODS_SERVICE_PURCHASE);
	}
}
