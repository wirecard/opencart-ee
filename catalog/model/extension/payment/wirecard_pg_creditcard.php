<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/opencart-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/opencart-ee/blob/master/LICENSE
 */

require_once(dirname( __FILE__ ) . '/wirecard_pg/gateway.php');

/**
 * Class ModelExtensionPaymentWirecardPGCreditCard
 *
 * CreditCard Transaction model
 *
 * @since 1.0.0
 */
class ModelExtensionPaymentWirecardPGCreditCard extends ModelExtensionPaymentGateway {

	/**
	 * @var string
	 * @since 1.0.0
	 */
	protected $type = 'creditcard';

	/**
	 * Gets the shipping details of the last order done by the customer.
	 *
	 * The last order is defined by having an order_status_id other than 0.
	 * OpenCart already creates orders while the user is completing the checkout process.
	 *
	 * @param \Cart\Customer $user
	 * @return array
	 * @since 1.1.0
	 */
	public function getLatestCustomerShipping($user) {
		return reset($this->db->query("SELECT
			shipping_firstname AS firstname, 
			shipping_lastname AS lastname, 
			shipping_company AS company,
			shipping_address_1 AS address_1,
			shipping_address_2 AS address_2,
			shipping_city AS city,
			shipping_zone_id AS zone_id,
			shipping_zone AS zone,
			shipping_country_id AS country_id,
			shipping_country AS country
			FROM `" . DB_PREFIX . "order`
			WHERE customer_id=" . $user->getId() . "
			AND order_status_id != 0
			ORDER BY order_id DESC
			LIMIT 1;")->rows);
	}
}
