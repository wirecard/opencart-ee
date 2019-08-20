<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/opencart-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/opencart-ee/blob/master/LICENSE
 */

use Wirecard\PaymentSdk\Entity\AccountInfo;
use Wirecard\PaymentSdk\Constant\AuthMethod;

class PGAccountInfo extends Model {
    protected $auth_method;
    protected $auth_timestamp;
    protected $order;
    protected $customer_id;

    /**
     * @return AccountInfo
     */
    public function createAccountInfo() {
        $this->auth_method    = AuthMethod::GUEST_CHECKOUT;
        $this->auth_timestamp = null;

        $this->load->model('account/customer');
        if ($this->isAuthenticatedUser()) {
            $this->setAuthenticated();
        }

        return $this->initializeAccountInfo();
    }

    protected function setCustomer() {
        //$this->load->model('account/customer');

        //$customer_info = $this->model_account_customer->getCustomer($order_info['customer_id']);
    }

    protected function initializeAccountInfo() {
        $accountInfo = new AccountInfo();
        $accountInfo->setAuthMethod($this->auth_method);
        $accountInfo->setAuthTimestamp($this->auth_timestamp);

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

    protected function fetchAuthenticationTimestamp() {
        //addLogin in upload/&catalog/model/account/customer.php
        //$this->db->query("INSERT INTO " . DB_PREFIX . "customer_ip SET customer_id = '" . (int)$customer_id . "', store_id = '" . (int)$this->config->get('config_store_id') . "', ip = '" . $this->db->escape($ip) . "', country = '" . $this->db->escape($country) . "', date_added = NOW()");
        $result = $this->db->query("SELECT * FROM `" . DB_PREFIX . "customer_ip` WHERE customer_id = '" . (int)$this->customer_id . "' ORDER BY date_added DESC LIMIT 1");
        // return login timestamp from db
        // customer_id in order
        return $result;
    }
}