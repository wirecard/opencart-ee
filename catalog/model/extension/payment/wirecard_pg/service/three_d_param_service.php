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
	 *
	 * @since 1.5.0
	 */
	public static function addThreeDsParameters($gateway, $registry, $transaction, $vault_token = null) {
		// Account Info
		$account_holder = $transaction->getAccountHolder();
		if ($account_holder instanceof \Wirecard\PaymentSdk\Entity\AccountHolder) {
			$account_info = new PGAccountInfo($registry, $gateway, $account_holder, $vault_token);
			$account_info->mapAccountInfo();
		}

		// Risk Info
		$risk_info = new PGRiskInfo($registry, $transaction);
		$risk_info->mapRiskInfo();

		// Hardcoded
		$transaction->setIsoTransactionType(\Wirecard\PaymentSdk\Constant\IsoTransactionType::GOODS_SERVICE_PURCHASE);
	}
}
