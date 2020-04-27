<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/opencart-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/opencart-ee/blob/master/LICENSE
 */

/**
 * Class ModelExtensionPaymentGateway
 *
 * @since 1.0.0
 */
class ModelExtensionPaymentWirecardPG extends Model {

	const VAULT_TABLE = 'wirecard_ee_vault';

	/**
	 * Create transaction table in install process
	 *
	 * @since 1.0.0
	 * @since 1.5.0 Add vault created_at and updated_at columns
	 */
	public function install() {
		$this->db->query("
          CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "wirecard_ee_transactions` (
            `tx_id` INT(11) NOT NULL AUTO_INCREMENT,
            `order_id` INT(11) NOT NULL,
            `transaction_id` VARCHAR(128) NOT NULL,
            `parent_transaction_id` VARCHAR(128) DEFAULT NULL,
            `transaction_type` VARCHAR(32) NOT NULL,
            `payment_method` VARCHAR(32) NOT NULL,
            `transaction_state` VARCHAR(32) NOT NULL,
            `amount` DECIMAL(10, 6) NOT NULL,
            `currency` VARCHAR(3) NOT NULL,
            `response` TEXT default NULL,
            `transaction_link` VARCHAR(255) default NULL,
			`date_added` DATETIME NOT NULL,
			`date_modified` DATETIME NOT NULL,
            PRIMARY KEY (`tx_id`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci");

		// This is added for those who have already installed the existing version of the extension.
		// It just changes the column type to a 6-digit decimal. Doing this right after the creation
		// of a table causes no harm since it just updates to the same type anyways.

		$query = $this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . "wirecard_ee_transactions` WHERE `Field` = 'xml'");

		if ($query->num_rows == 0) {
			$this->db->query("
			ALTER TABLE `" . DB_PREFIX . "wirecard_ee_transactions`
			MODIFY COLUMN `amount` DECIMAL(10, 6) NOT NULL,
            ADD `xml` TEXT default NULL;
		    ");
		}

		$this->db->query("
			CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . self::VAULT_TABLE . "` (
			`vault_id` INT(10) unsigned NOT NULL AUTO_INCREMENT,
			`user_id` INT(10) NOT NULL,
			`address_id` INT(10) NOT NULL,
			`token` VARCHAR(20) NOT NULL,
			`masked_pan` VARCHAR(30) NOT NULL,
			`expiration_month` INT(10) NOT NULL,
			`expiration_year` INT(10) NOT NULL,
			PRIMARY KEY (`vault_id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;");

		$this->updateVaultTable();
	}

	public function updateVaultTable() {
		$vault_query = $this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . self::VAULT_TABLE . "` LIKE 'created_at'");

		if ($vault_query->num_rows == 0) {
			$this->db->query("
                ALTER TABLE `" . DB_PREFIX . self::VAULT_TABLE . "`
                ADD COLUMN `date_added` timestamp NOT NULL default CURRENT_TIMESTAMP,
                ADD COLUMN `date_updated` timestamp NULL ON UPDATE CURRENT_TIMESTAMP
		    ");
		}
	}

	/**
	 * Create transaction entry
	 *
	 * @param \Wirecard\PaymentSdk\Response\SuccessResponse $response
	 * @param array $order
	 * @param string $transaction_state
	 * @param ControllerExtensionPaymentGateway $payment_controller
	 * @since 1.0.0
	 */
	public function createTransaction($response, $order, $transaction_state, $payment_controller) {
		$amount = $response->getData()['requested-amount'];
		$order_id = $response->getCustomFields()->get('orderId');
		$currency = $order['currency_code'];

		$parent_transaction_id = $this->checkParentTransaction($response, $amount);

		$this->db->query("
            INSERT INTO `" . DB_PREFIX . "wirecard_ee_transactions` SET 
            `order_id` = '" . (int)$order_id . "', 
            `transaction_id` = '" . $this->db->escape($response->getTransactionId()) . "', 
            `parent_transaction_id` = '" . $this->db->escape($parent_transaction_id) . "', 
            `transaction_type` = '" . $this->db->escape($response->getTransactionType()) . "',
            `payment_method` = '" . $this->db->escape($payment_controller->getType()) . "', 
            `transaction_state` = '" . $this->db->escape($transaction_state) . "',
            `amount` = '" . (float)$amount . "',
            `currency` = '" . $this->db->escape($currency) . "',
            `response` = '" . $this->db->escape(json_encode($response->getData())) . "',
            `xml` = '" . $this->db->escape($response->getRawData()) . "',
            `date_added` = NOW()
            ");
	}

	/**
	 * Get transaction list
	 *
	 * @return array
	 * @since 1.0.0
	 */
	public function getTransactionList() {
		$transactions = $this->db->query("
            SELECT * FROM `" . DB_PREFIX . "wirecard_ee_transactions` ORDER BY tx_id DESC
            ")->rows;

		return $transactions;
	}

	/**
	 * Get transaction via transaction id
	 *
	 * @param string $transaction_id
	 * @return bool|array
	 * @since 1.0.0
	 */
	public function getTransaction($transaction_id) {
		$query = $this->db->query("
	        SELECT * FROM `" . DB_PREFIX . "wirecard_ee_transactions` WHERE `transaction_id` = '" . $this->db->escape($transaction_id) . "'
	    ");

		if ($query->num_rows) {
			return $query->row;
		}

		return false;
	}

	/**
	 * Get all follow up transactions and calculate rest amount
	 *
	 * @param string $transaction_id
	 * @param float $amount
	 * @return float
	 * @since 1.0.0
	 */
	public function getTransactionMaxAmount($transaction_id, $amount = 0) {
		$base_amount = $this->db->query("
	    SELECT amount FROM `" . DB_PREFIX ."wirecard_ee_transactions` WHERE `transaction_id` = '" . $this->db->escape($transaction_id) . "'
	    ")->row['amount'];

		$follow_amounts = $this->db->query("
	    SELECT amount FROM `" . DB_PREFIX . "wirecard_ee_transactions` WHERE `parent_transaction_id` = '" . $this->db->escape($transaction_id) . "'
	    ")->rows;

		foreach ($follow_amounts as $value) {
			$base_amount -= $value['amount'];
		}
		$base_amount -= $amount;

		return $base_amount;
	}

	/**
	 * Check for existing parent transaction and close it
	 *
	 * @param $response
	 * @return string|null
	 * @since 1.0.0
	 */
	public function checkParentTransaction($response, $amount) {
		$parent_transaction_id = null;
		$parent_transaction = $this->getTransaction($response->getParentTransactionId());

		if ($parent_transaction) {
			$parent_transaction_id = $response->getParentTransactionId();
			$rest_amount = $this->getTransactionMaxAmount($parent_transaction_id, $amount);
			if ($rest_amount <= 0) {
				$this->updateTransactionState($parent_transaction_id, 'closed');
			}
		}

		return $parent_transaction_id;
	}

	/**
	 * Update transaction with specific transactionstate
	 *
	 * @param string $transaction_id
	 * @param $transaction_state
	 * @since 1.0.0
	 */
	public function updateTransactionState($transaction_id, $transaction_state) {
		$this->db->query("
        UPDATE `" . DB_PREFIX . "wirecard_ee_transactions` SET 
            `transaction_state` = '" . $this->db->escape($transaction_state) . "', 
            `date_modified` = NOW() WHERE 
            `transaction_id` = '" . $this->db->escape($transaction_id) . "'
        ");
	}

	/**
	 * Get all child transactions to specific transaction id
	 *
	 * @param string $transaction_id
	 * @return mixed
	 * @since 1.1.0
	 */
	public function getChildTransactions($transaction_id) {
		$child_transactions = $this->db->query("
	    SELECT * FROM `" . DB_PREFIX ."wirecard_ee_transactions` WHERE `parent_transaction_id` = '" . $this->db->escape($transaction_id) . "'
	    ")->rows;

		return $child_transactions;
	}
}
