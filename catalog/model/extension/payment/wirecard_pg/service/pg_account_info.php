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
	/** @var ControllerExtensionPaymentGateway */
	protected $gateway;
	protected $new_cart_vault_request;

	/**
	 * @return AccountInfo
	 */
	public function createAccountInfo($gateway, $new_cart_vault_request) {
		// Authentication method and timestamp
		$this->auth_method    = AuthMethod::GUEST_CHECKOUT;
		$this->auth_timestamp = null;
		$this->gateway        = $gateway;
		$this->load->model('account/customer');
		$this->new_cart_vault_request = $new_cart_vault_request;
		if ($this->isAuthenticatedUser()) {
			$this->setAuthenticated();
		}

		// Challenge Indicator
		$this->setChallengeIndicator();

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

	protected function setChallengeIndicator() {
		$challenge_indicator = $this->fetchChallengeIndicator();
		// Check if first time oneclick - if true change indicator to challenge_threed
		if (isset($this->new_cart_vault_request)) {
			$challenge_indicator = 'challenge_threed';
		}
		$challenge_indicator = $this->mapChallengeIndicator($challenge_indicator);

		$this->challenge_indicator = $challenge_indicator;
	}

	protected function fetchChallengeIndicator() {
		$challenge_indicator = $this->gateway->getShopConfigVal('challenge_indicator');

		return $challenge_indicator;
	}

	protected function mapChallengeIndicator($challenge_indicator) {
		switch ($challenge_indicator) {
			case 'no_challenge':
				$indicator = ChallengeInd::NO_CHALLENGE;
				break;
			case 'challenge_threed':
				$indicator = ChallengeInd::CHALLENGE_THREED;
				break;
			case 'challenge_mandate':
				$indicator = ChallengeInd::CHALLENGE_MANDATE;
				break;
			default:
				$indicator = ChallengeInd::NO_PREFERENCE;
				break;
		}

		return $indicator;
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
