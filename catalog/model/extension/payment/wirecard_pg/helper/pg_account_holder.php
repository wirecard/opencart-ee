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

use Wirecard\PaymentSdk\Entity\AccountHolder;
use Wirecard\PaymentSdk\Entity\Address;

/**
 * Class PGAccountHolder
 *
 * @since 1.0.0
 */
class PGAccountHolder {

	const BILLING = 'billing';
	const SHIPPING = 'shipping';

	/**
	 * Create AccountHolder with specific address data
	 *
	 * @param array $order
	 * @param string $type
	 * @return AccountHolder
	 * @since 1.0.0
	 */
	public function createAccountHolder($order, $type = self::BILLING) {
		$accountHolder = new AccountHolder();
		if (self::SHIPPING == $type) {
			$accountHolder->setAddress($this->createAddressData($order, $type));
			$accountHolder->setFirstName($order['shipping_firstname']);
			$accountHolder->setLastName($order['shipping_lastname']);
		} else {
			$accountHolder->setAddress($this->createAddressData($order, $type));
			$accountHolder->setFirstName($order['payment_firstname']);
			$accountHolder->setLastName($order['payment_lastname']);
			$accountHolder->setEmail($order['email']);
			$accountHolder->setPhone($order['telephone']);
			// following data is not available
			//$accountHolder->setDateOfBirth();
			//$accountHolder->setGender();
		}

		return $accountHolder;
	}

	/**
	 * Create Address data based on order
	 *
	 * @param array $order
	 * @param string $type
	 * @return Address
	 * @since 1.0.0
	 */
	public function createAddressData($order, $type) {
		if (self::SHIPPING == $type) {
			$address = new Address( $order['shipping_iso_code_2'], $order['shipping_city'], $order['shipping_address_1']);
			$address->setPostalCode($order['shipping_postcode']);
		} else {
			$address = new Address($order['payment_iso_code_2'], $order['payment_city'], $order['payment_address_1']);
			$address->setPostalCode($order['payment_postcode']);
			if (strlen($order['payment_address_2'])) {
				$address->setStreet2($order['payment_address_2']);
			}
		}

		return $address;
	}
}
