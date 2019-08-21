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
	 * @param Registry $registry
	 * @return \Wirecard\PaymentSdk\Entity\AccountInfo
	 */
	public static function getAccountInfo($registry) {
		$accountInfo = new PGAccountInfo($registry);

		return $accountInfo->createAccountInfo();
	}
}
