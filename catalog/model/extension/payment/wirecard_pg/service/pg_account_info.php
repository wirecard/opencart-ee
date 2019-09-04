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

require_once(dirname(__FILE__) . '/../vault.php');

class PGAccountInfo extends Model {
	const SDK_DATE_FORMAT = 'Y-m-d\TH:i:s\Z';
	const DB_DATE_FORMAT = 'Y-m-d H:i:s';

	/** @var ControllerExtensionPaymentGateway $gateway */
	protected $gateway;
	/** @var int $customer_id */
	protected $customer_id;
	/** @var string $auth_method */
	protected $auth_method;
	/** @var null|DateTime $auth_timestamp */
	protected $auth_timestamp;
	/** @var string $challenge_indicator */
	protected $challenge_indicator;
	/** @var AccountHolder $account_holder */
	protected $account_holder;
	/** @var bool $one_click_checkout */
	protected $vault_token;

	public function __construct($registry, $gateway, $account_holder, $vault_token) {
		parent::__construct($registry);
		$this->load->model('account/customer');
		$this->gateway = $gateway;
		$this->account_holder = $account_holder;
		$this->vault_token = $vault_token;
	}

	/**
	 * Create SDK\AccountInfo
	 * Map all existing data
	 *
	 * @return AccountInfo
	 *
	 * @since 1.5.0
	 */
	public function mapAccountInfo() {
		// Set auth method and auth timestamp
		$this->setAuthenticatedData();
		// Challenge Indicator
		$this->setChallengeIndicator();

		// Map all settings and create SDK account info
		$this->account_holder->setAccountInfo($this->initializeAccountInfo());
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
		$account_info = new AccountInfo();

		$account_info->setAuthMethod($this->auth_method);
		$account_info->setAuthTimestamp($this->auth_timestamp);
		$account_info->setChallengeInd($this->challenge_indicator);

		if ($this->isAuthenticatedUser()) {
			$this->addAccountInfoData($account_info);
		}

		return $account_info;
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
		$this->challenge_indicator = $this->fetchChallengeIndicator();
	}

	/**
	 * Add database information to given AccountHolder AccountInfo
	 * For authenticated User
	 *
	 * @param AccountInfo $account_info
	 *
	 * @since 1.5.0
	 */
	protected function addAccountInfoData($account_info) {
		$account_info->setCreationDate($this->fetchAccountCreationDate());
		$account_info->setAmountTransactionsLastDay($this->fetchTransactionsLastDay());
		$account_info->setAmountTransactionsLastYear($this->fetchTransactionsLastYear());
		$account_info->setAmountPurchasesLastSixMonths($this->fetchPurchasesLastSixMonths());
		if (isset($this->vault_token)) {
			$account_info->setCardCreationDate($this->fetchCardCreationDate());
		}
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
			$time_stamp = $this->reformatDateString(
				$result['date_added'],
				self::DB_DATE_FORMAT,
				self::SDK_DATE_FORMAT
			);
		}

		return $time_stamp;
	}

	/**
	 * Reformat a date string
	 *
	 * @param $date
	 * @param $previous_format
	 * @param $new_format
	 * @return string
	 *
	 * @since 1.5.0
	 */
	protected function reformatDateString($date, $previous_format, $new_format) {
		$date = DateTime::createFromFormat($previous_format, $date);

		return $date->format($new_format);
	}

	/**
	 * Select account creation date
	 * For authenticated user
	 *
	 * @return DateTime
	 *
	 * @since 1.5.0
	 */
	protected function fetchAccountCreationDate() {
		$creation_date = new DateTime();

		$result = $this->db->query("SELECT date_added FROM `" . DB_PREFIX . "customer` WHERE customer_id = '" . (int)$this->customer_id . "'");
		if ($result->num_rows) {
			$creation_date = $this->reformatDateString(
				$result->row['date_added'],
				self::DB_DATE_FORMAT,
				self::SDK_DATE_FORMAT
			);
		}

		return $creation_date;
	}

	/**
	 * Select card creation date
	 * For vaulted card
	 *
	 * @return DateTime
	 *
	 * @since 1.5.0
	 */
	protected function fetchCardCreationDate() {
		$creation_date = new DateTime();

		if (!$this->vaultContainsCreatedAt()) {
			return $creation_date;
		}

		$result = $this->db->query("SELECT date_added FROM `" . DB_PREFIX . ModelExtensionPaymentWirecardPGVault::VAULT_TABLE . "` WHERE token = '" . (int)$this->vault_token . "'");
		if ($result->num_rows) {
			$creation_date = DateTime::createFromFormat('Y-m-d H:i:s', $result->row['date_added']);
		}

		return $creation_date;
	}

	protected function vaultContainsCreatedAt() {
		$contains_created_at = true;
		$vault_query = $this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . ModelExtensionPaymentWirecardPGVault::VAULT_TABLE . "` LIKE 'created_at'");
		if ($vault_query->num_rows == 0) {
			$contains_created_at = false;
		}

		return $contains_created_at;

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
