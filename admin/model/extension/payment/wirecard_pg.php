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

	/**
	 * Create transaction table in install process
	 *
	 * @since 1.0.0
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
            `amount` DECIMAL(10, 2) NOT NULL,
            `currency` VARCHAR(3) NOT NULL,
            `response` TEXT default NULL,
            `transaction_link` VARCHAR(255) default NULL,
			`date_added` DATETIME NOT NULL,
			`date_modified` DATETIME NOT NULL,
            PRIMARY KEY (`tx_id`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci");

		$this->db->query("
			CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "wirecard_ee_vault` (
			`vault_id` INT(10) unsigned NOT NULL AUTO_INCREMENT,
			`user_id` INT(10) NOT NULL,
			`token` VARCHAR(20) NOT NULL,
			`masked_pan` VARCHAR(30) NOT NULL,
			PRIMARY KEY (`vault_id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;");
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
}
