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
    ///**
    // * @param Transaction $transaction
    // * Could be used to add all params here
    // */
    /*public function addThreeDParameters($transaction) {
        // check if guest checkout, authenticated checkout or oneclick checkout
        // pass to creators
        // return everything as array
        // merge return into request
        $transaction->getAccountHolder()->setAccountInfo($this->getAccountInfo());
    }*/

    //pass additional_information_helper model and get $order, db, etc.
    //with $this->model->db->sidafsid
    /*
		 @var AdditionalInformationHelper model
$this->model = $model;
$this->sum = 0;*/
    /**
     * @param Registry $registry
     * @return \Wirecard\PaymentSdk\Entity\AccountInfo
     */
    public static function getAccountInfo($registry) {
        $accountInfo = new PGAccountInfo($registry);

        return $accountInfo->createAccountInfo();
    }
}