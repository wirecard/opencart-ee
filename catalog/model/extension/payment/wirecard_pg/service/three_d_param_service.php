<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/opencart-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/opencart-ee/blob/master/LICENSE
 */

require_once(dirname(__FILE__) . '/pg_account_info.php');
include_once(DIR_SYSTEM . 'library/autoload.php');

class ThreeDParamService {
	/**
	 * @param ControllerExtensionPaymentGateway $gateway
	 * @param Registry $registry
	 * @param \Wirecard\PaymentSdk\Transaction\Transaction $transaction
	 * @return mixed
	 */
	public static function addThreeDsParameters($gateway, $registry, $transaction, $new_card_vault_request) {
		$accountInfo = new PGAccountInfo($registry, $new_card_vault_request);
		$transaction->getAccountHolder()->setAccountInfo($accountInfo->createAccountInfo($gateway, $new_card_vault_request));

		return $transaction;
	}
}