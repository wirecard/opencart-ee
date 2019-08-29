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
	 * Adds three ds parameters to the passed transaction
	 *
	 * @param ControllerExtensionPaymentGateway $gateway
	 * @param Registry $registry
	 * @param \Wirecard\PaymentSdk\Transaction\Transaction $transaction
	 * @param bool $new_card_vault_request
	 *
	 * @since 1.5.0
	 */
	public static function addThreeDsParameters($gateway, $registry, $transaction, $new_card_vault_request) {
		$account_holder = $transaction->getAccountHolder();
		if ($account_holder instanceof \Wirecard\PaymentSdk\Entity\AccountHolder) {
			$accountInfo = new PGAccountInfo($registry, $gateway, $account_holder, $new_card_vault_request);
			$account_holder->setAccountInfo($accountInfo->createAccountInfo());
		}
	}
}