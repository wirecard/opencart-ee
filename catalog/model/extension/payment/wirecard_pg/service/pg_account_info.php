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
	/* Helper props */
	protected $order;
	/** @var ControllerExtensionPaymentGateway */
	protected $gateway;
	// Credit card tokenization
	protected $new_cart_vault_request;
	// Customer id
	protected $customer_id;

	/* Mappables */
	// Authentication method
	protected $auth_method;
	// Authentication timestamp
	protected $auth_timestamp;
	// Challenge indicator
	protected $challenge_indicator;

	// Account creation date
	protected $creation_date;
	// Account updated date
	protected $update_date;
	// If vaulted credit card, date it was saved
	protected $card_creation_date;
	// Transactions last day
	protected $transactions_last_day;
	// Transactions last year
	protected $transactions_last_year;
	// Card transactions last day
	protected $card_transactions_last_day;
	// Purchases last six months
	protected $purchases_last_six_months;


	/**
	 * @param $gateway
	 * @param $new_cart_vault_request
	 * @return AccountInfo
	 * @throws Exception
	 */
	public function createAccountInfo($gateway, $new_cart_vault_request) {
		// Authentication method and timestamp
		$this->gateway        = $gateway;
		$this->load->model('account/customer');
		$this->new_cart_vault_request = $new_cart_vault_request;

		// Set auth method and auth timestamp
		$this->setAuthenticated();
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

		if ($this->isAuthenticatedUser()) {
			$this->setAccountData($accountInfo);
		}

		return $accountInfo;
	}

	protected function setAuthenticated() {
		$this->auth_method = AuthMethod::GUEST_CHECKOUT;
		$this->auth_timestamp = null;
		if ($this->isAuthenticatedUser()) {
			$this->setCustomerId($this->customer->getId());
			$this->auth_method = AuthMethod::USER_CHECKOUT;
			$this->auth_timestamp = $this->fetchAuthenticationTimestamp();
		}
	}

	protected function isAuthenticatedUser() {
		$is_authenticated = false;

		if ($this->customer->isLogged()) {
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
			$challenge_indicator = 'challenge_mandate';
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

	/**
	 * @param AccountInfo $accountInfo
	 */
	protected function setAccountData($accountInfo) {
		$accountInfo->setCreationDate($this->fetchAccountCreationDate());
		$accountInfo->setAmountTransactionsLastDay($this->fetchTransactionsLastDay());
		$accountInfo->setAmountTransactionsLastYear($this->fetchTransactionsLastYear());
		$accountInfo->setAmountPurchasesLastSixMonths($this->fetchPurchasesLastSixMonths());
	}

	protected function fetchAccountCreationDate() {
		$creation_date = new DateTime();

		$result = $this->db->query("SELECT date_added FROM `" . DB_PREFIX . "customer` WHERE customer_id = '" . (int)$this->customer_id . "'");
		if ($result->num_rows) {
			$creation_date = DateTime::createFromFormat('Y-m-d H:i:s', $result->row['date_added']);
		}

		return $creation_date;
	}

	protected function fetchTransactionsLastDay() {
		$table = 'customer_transaction';
		$yesterday_start = new DateTime();
		$yesterday_start->add(DateInterval::createFromDateString('yesterday'));
		$yesterday_start = $yesterday_start->setTime(0, 1);

		$yesterday_end = clone ($yesterday_start);
		$yesterday_end = $yesterday_end->setTime(23, 59, 59);
		$transactions_last_day = $this->fetchCountForDate(
			$table,
			$yesterday_start->format('Y-m-d H:i:s'),
			$yesterday_end->format('Y-m-d H:i:s')
		);

		return $transactions_last_day;
	}

	protected function fetchTransactionsLastYear() {
		$table = 'customer_transaction';
		$last_year = date('Y', strtotime('-1 year'));
		$date_start = $last_year . '-01-01';
		$date_end = $last_year . '-12-31';

		return $this->fetchCountForDate($table, $date_start, $date_end);
	}

	protected function fetchPurchasesLastSixMonths() {
		//@TODO Add order_status (check oc_order_status for available) check
		$table = 'order';
		$six_months_ago = date('Y-m-d', strtotime('-6 months'));
		$today = date('Y-m-d');

		return $this->fetchCountForDate($table, $six_months_ago, $today);
	}

	private function fetchCountForDate($table, $date_start, $date_end) {
		$total = 0;

		$result = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . $table . " WHERE customer_id = '" . (int)$this->customer_id . "' AND date_added BETWEEN '" . $this->db->escape($date_start) . "' AND '" . $this->db->escape($date_end) . "'");
		if ($result->num_rows) {
			$total = $result->row['total'];
		}

		return $total;
	}

	protected function getMerchantCrmId() {
		// MOVE TO ACCOUNT HOLDER!
	}
}
