<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/opencart-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/opencart-ee/blob/master/LICENSE
 */

use Wirecard\PaymentSdk\Constant\ChallengeInd;
use Wirecard\PaymentSdk\Entity\AccountInfo;
use Wirecard\PaymentSdk\Constant\AuthMethod;

class PGAccountInfo extends Model {
    protected $auth_method;
    protected $auth_timestamp;
    protected $order;
    protected $customer_id;
    protected $challenge_indicator;

    /**
     * @return AccountInfo
     */
    public function createAccountInfo() {
        // Authentication method and timestamp
        $this->auth_method    = AuthMethod::GUEST_CHECKOUT;
        $this->auth_timestamp = null;
        $this->load->model('account/customer');
        if ($this->isAuthenticatedUser()) {
            $this->setAuthenticated();
        }

        // Challenge Indicator
        $this->challenge_indicator = $this->getChallengeIndicator();

        // Map all settings and create SDK account info
        return $this->initializeAccountInfo();
    }

    protected function initializeAccountInfo() {
        $accountInfo = new AccountInfo();
        $accountInfo->setAuthMethod($this->auth_method);
        $accountInfo->setAuthTimestamp($this->auth_timestamp);
        $accountInfo->setChallengeInd($this->challenge_indicator);

        return $accountInfo;
    }

    protected function setAuthenticated() {
        $this->auth_method    = AuthMethod::USER_CHECKOUT;
        $this->auth_timestamp = $this->fetchAuthenticationTimestamp();
    }

    protected function isAuthenticatedUser() {
        $is_authenticated = false;

        if ($this->customer->isLogged()) {
            $this->setCustomerId($this->customer->getId());
            $is_authenticated = true;
        }

        return $is_authenticated;
    }

    protected function setCustomerId($customer_id) {
        $this->customer_id = $customer_id;
    }

    protected function getChallengeIndicator() {
        $challenge_indicator =  ChallengeInd::NO_PREFERENCE;
        // fetch from db
        $challenge_indicator = $this->fetchChallengeIndicator();
        // If save creditcard (vault) checkout overwrite with CHALLENGE_MANDATE
        $challenge_indicator = ChallengeInd::CHALLENGE_MANDATE;

        return $challenge_indicator;
    }

    protected function fetchChallengeIndicator() {
      return '';//FETCH FROM DB
    }

    protected function fetchAuthenticationTimestamp() {
        $time_stamp = null;

        $result = $this->db->query("SELECT * FROM `" . DB_PREFIX . "customer_online` WHERE customer_id = '" . (int)$this->customer_id . "' ORDER BY date_added ASC LIMIT 1");
        if ($result->num_rows) {
            $time_stamp = DateTime::createFromFormat('Y-m-d H:i:s', $result->row['date_added']);
            $time_stamp->format('Y-m-d\TH:i:s\Z');
        }
        return $time_stamp;
    }
}