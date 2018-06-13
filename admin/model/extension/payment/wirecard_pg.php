<?php
/**
 * Shop System Plugins - Terms of Use
 *
 * The plugins offered are provided free of charge by Wirecard AG and are explicitly not part
 * of the Wirecard AG range of products and services.
 *
 * They have been tested and approved for full functionality in the standard configuration
 * (status on delivery) of the corresponding shop system. They are under General Public
 * License version 3 (GPLv3) and can be used, developed and passed on to third parties under
 * the same terms.
 *
 * However, Wirecard AG does not provide any guarantee or accept any liability for any errors
 * occurring when used in an enhanced, customized shop system configuration.
 *
 * Operation in an enhanced, customized configuration is at your own risk and requires a
 * comprehensive test phase by the user of the plugin.
 *
 * Customers use the plugins at their own risk. Wirecard AG does not guarantee their full
 * functionality neither does Wirecard AG assume liability for any disadvantages related to
 * the use of the plugins. Additionally, Wirecard AG does not guarantee the full functionality
 * for customized shop systems or installed plugins of other vendors of plugins within the same
 * shop system.
 *
 * Customers are responsible for testing the plugin's functionality before starting productive
 * operation.
 *
 * By installing the plugin into the shop system the customer agrees to these terms of use.
 * Please do not use the plugin if you do not agree to these terms of use!
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
	}

	/**
	 * Create transaction entry
	 *
	 * @param \Wirecard\PaymentSdk\Response\SuccessResponse $response
     * @param array $order
	 * @param string $transactionState
	 * @param string $paymentMethod
	 * @since 1.0.0
	 */
    public function createTransaction($response, $order, $transactionState, $paymentMethod) {
        $amount = $response->getData()['requested-amount'];
        $orderId = $response->getCustomFields()->get('orderId');
        $currency = $order['currency_code'];

        $this->db->query("
            INSERT INTO `" . DB_PREFIX . "wirecard_ee_transactions` SET 
            `order_id` = '" . (int)$orderId . "', 
            `transaction_id` = '" . $this->db->escape($response->getTransactionId()) . "', 
            `parent_transaction_id` = '" . $this->db->escape($response->getParentTransactionId()) . "', 
            `transaction_type` = '" . $this->db->escape($response->getTransactionType()) . "',
            `payment_method` = '" . $this->db->escape($paymentMethod) . "', 
            `transaction_state` = '" . $this->db->escape($transactionState) . "',
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
            SELECT * FROM `" . DB_PREFIX . "wirecard_ee_transactions` ORDER BY tx_id
            ")->rows;

		return $transactions;
	}
}
