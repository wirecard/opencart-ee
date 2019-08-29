<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/opencart-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/opencart-ee/blob/master/LICENSE
 */

use Wirecard\PaymentSdk\Constant\ChallengeInd;
use Wirecard\PaymentSdk\Entity\AccountHolder;
use Wirecard\PaymentSdk\Entity\AccountInfo;
use Wirecard\PaymentSdk\Constant\AuthMethod;

class PGAccountInfo extends Model {
	/** @var ControllerExtensionPaymentGateway $gateway */
	protected $gateway;
	/** @var bool $new_cart_vault_request */
	protected $new_cart_vault_request;
	/** @var int $customer_id */
	protected $customer_id;
	/** @var string $auth_method */
	protected $auth_method;
	/** @var null|DateTime $auth_timestamp */
	protected $auth_timestamp;
	/** @var string $challenge_indicator */
	protected $challenge_indicator;

	// If vaulted credit card, date it was saved
	protected $card_creation_date; //@TODO Add
	/** @var AccountHolder $account_holder */
	protected $account_holder;


	public function __construct($registry, $gateway, $new_cart_vault_request, $account_holder) {
		parent::__construct($registry);
		$this->load->model('account/customer');
		$this->gateway = $gateway;
		$this->new_cart_vault_request = $new_cart_vault_request;
		$this->account_holder = $account_holder;
	}

	/**
	 * Create SDK\AccountInfo
	 * Map all existing data
	 *
	 * @return AccountInfo
	 *
	 * @since 1.5.0
	 */
	public function createAccountInfo() {
		// Set auth method and auth timestamp
		$this->setAuthenticatedData();
		// Challenge Indicator
		$this->setChallengeIndicator();

		// Map all settings and create SDK account info
		return $this->initializeAccountInfo();
	}

	/**
	 * Initialize SDK\AccountInfo
	 * Add all available data
	 *
	 * @return AccountInfo
	 *
	 * @since 1.5.0
	 */
	protected function initializeAccountInfo() {
		$accountInfo = new AccountInfo();

		$accountInfo->setAuthMethod($this->auth_method);
		$accountInfo->setAuthTimestamp($this->auth_timestamp);
		$accountInfo->setChallengeInd($this->challenge_indicator);

		if ($this->isAuthenticatedUser()) {
			$this->addAccountInfoData($accountInfo);
		}

		return $accountInfo;
	}

	/**
	 * Set authenticated customer Id
	 *
	 * @param $customer_id
	 *
	 * @since 1.5.0
	 */
	protected function setCustomerId($customer_id) {
		$this->customer_id = $customer_id;
	}

	/**
	 * Set authentication method and authentication timestamp
	 * If user is not authenticated, set guest checkout params
	 *
	 * @since 1.5.0
	 */
	protected function setAuthenticatedData() {
		$this->auth_method = AuthMethod::GUEST_CHECKOUT;
		$this->auth_timestamp = null;
		if ($this->isAuthenticatedUser()) {
			$customer_id = $this->customer->getId();
			$this->setCustomerId($customer_id);
			$this->auth_method = AuthMethod::USER_CHECKOUT;
			$this->auth_timestamp = $this->fetchAuthenticationTimestamp();
			$this->addAccountHolderCrmId($customer_id);
		}
	}

	/**
	 * Check if user is authenticated (logged in)
	 *
	 * @return bool
	 *
	 * @since 1.5.0
	 */
	protected function isAuthenticatedUser() {
		$is_authenticated = false;

		if ($this->customer->isLogged()) {
			$is_authenticated = true;
		}

		return $is_authenticated;
	}

	/**
	 * Set challenge indicator
	 *
	 * @since 1.5.0
	 */
	protected function setChallengeIndicator() {
		$challenge_indicator = $this->fetchChallengeIndicator();
		// Check if first time oneclick - if true change indicator to challenge_threed
		if (isset($this->new_cart_vault_request)) {
			$challenge_indicator = 'challenge_mandate';
		}
		$challenge_indicator = $this->mapChallengeIndicator($challenge_indicator);

		$this->challenge_indicator = $challenge_indicator;
	}

	/**
	 * Add database information to given AccountHolder AccountInfo
	 * For authenticated User
	 *
	 * @param AccountInfo $accountInfo
	 *
	 * @since 1.5.0
	 */
	protected function addAccountInfoData($accountInfo) {
		$accountInfo->setCreationDate($this->fetchAccountCreationDate());
		$accountInfo->setAmountTransactionsLastDay($this->fetchTransactionsLastDay());
		$accountInfo->setAmountTransactionsLastYear($this->fetchTransactionsLastYear());
		$accountInfo->setAmountPurchasesLastSixMonths($this->fetchPurchasesLastSixMonths());
		//@TODO add card creation date
	}

	/**
	 * Select configured challenge indicator
	 *
	 * @return bool|string
	 *
	 * @since 1.5.0
	 */
	protected function fetchChallengeIndicator() {
		$challenge_indicator = $this->gateway->getShopConfigVal('challenge_indicator');

		return $challenge_indicator;
	}

	/**
	 * Select authentication timestamp
	 * For authenticated user
	 *
	 * @return bool|DateTime|null
	 *
	 * @since 1.5.0
	 */
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
	 * Select account creation date
	 * For authenticated user
	 *
	 * @return bool|DateTime
	 *
	 * @since 1.5.0
	 */
	protected function fetchAccountCreationDate() {
		$creation_date = new DateTime();

		$result = $this->db->query("SELECT date_added FROM `" . DB_PREFIX . "customer` WHERE customer_id = '" . (int)$this->customer_id . "'");
		if ($result->num_rows) {
			$creation_date = DateTime::createFromFormat('Y-m-d H:i:s', $result->row['date_added']);
		}

		return $creation_date;
	}

	/**
	 * Select occured transactions
	 * For authenticated user
	 * On previous day
	 *
	 * @return int
	 *
	 * @since 1.5.0
	 */
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

	/**
	 * Select occured transactions
	 * For authenticated user
	 * In previous year
	 *
	 * @return int
	 *
	 * @since 1.5.0
	 */
	protected function fetchTransactionsLastYear() {
		$table = 'customer_transaction';
		$last_year = date('Y', strtotime('-1 year'));
		$date_start = $last_year . '-01-01';
		$date_end = $last_year . '-12-31';

		return $this->fetchCountForDate($table, $date_start, $date_end);
	}

	/**
	 * Select successful purchases done
	 * For authenticated user
	 * In last six months
	 *
	 * @return int
	 *
	 * @since 1.5.0
	 */
	protected function fetchPurchasesLastSixMonths() {
		//@TODO Add order_status (check oc_order_status for available) check
		$table = 'order';
		$six_months_ago = date('Y-m-d', strtotime('-6 months'));
		$today = date('Y-m-d');

		return $this->fetchCountForDate($table, $six_months_ago, $today);
	}

	/**
	 * Select count for given table
	 * For authenticated user
	 * Between given start date and end date
	 *
	 * @param $table
	 * @param $date_start
	 * @param $date_end
	 * @return int
	 *
	 * @since 1.5.0
	 */
	private function fetchCountForDate($table, $date_start, $date_end) {
		$total = 0;

		$result = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . $table . " WHERE customer_id = '" . (int)$this->customer_id . "' AND date_added BETWEEN '" . $this->db->escape($date_start) . "' AND '" . $this->db->escape($date_end) . "'");
		if ($result->num_rows) {
			$total = $result->row['total'];
		}

		return $total;
	}

	/**
	 * Map given indicator to SDK states
	 *
	 * @param $challenge_indicator
	 * @return string
	 *
	 * @since 1.5.0
	 */
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

	/**
	 * Add crm id to account holder
	 *
	 * @param $customer_id
	 *
	 * @since 1.5.0
	 */
	protected function addAccountHolderCrmId($customer_id) {
		$this->account_holder->setCrmId($customer_id);
	}
}
