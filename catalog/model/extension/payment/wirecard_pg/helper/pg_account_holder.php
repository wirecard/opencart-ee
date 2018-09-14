<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/opencart-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/opencart-ee/blob/master/LICENSE
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
	 * @param string|null   $birthdate
	 * @return AccountHolder
	 * @since 1.0.0
	 */
	public function createAccountHolder($order, $type = self::BILLING, $birthdate = null) {
		$account_holder = new AccountHolder();

		$account_holder->setAddress($this->createAddressData($order, $type));
		$account_holder->setFirstName($order['payment_firstname']);
		$account_holder->setLastName($order['payment_lastname']);
		$account_holder->setEmail($order['email']);
		$account_holder->setPhone($order['telephone']);
		if (!is_null($birthdate)) {
			$account_holder->setDateOfBirth(new \DateTime($birthdate));
		}
		if (self::SHIPPING == $type) {
			$account_holder->setAddress($this->createAddressData($order, $type));
			$account_holder->setFirstName($order['shipping_firstname']);
			$account_holder->setLastName($order['shipping_lastname']);
		}

		return $account_holder;
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
		    $stateIsoCode = $this->mapStateToIsoCode($order['shipping_iso_code_2'], $order['shipping_zone_code']);
			$address = new Address( $order['shipping_iso_code_2'], $order['shipping_city'], $order['shipping_address_1']);
			$address->setPostalCode($order['shipping_postcode']);
			$address->setState($stateIsoCode);
		} else {
            $stateIsoCode = $this->mapStateToIsoCode($order['payment_iso_code_2'], $order['payment_zone_code']);
			$address = new Address($order['payment_iso_code_2'], $order['payment_city'], $order['payment_address_1']);
			$address->setPostalCode($order['payment_postcode']);
            $address->setState($stateIsoCode);

			if (strlen($order['payment_address_2'])) {
				$address->setStreet2($order['payment_address_2']);
			}
		}

		return $address;
	}

    /**
     * Maps OpenCart state codes to ISO where necessary.
     *
     * @param $country
     * @param $state
     * @return string
     * @since 1.2.0
     */
	public function mapStateToIsoCode($country, $state) {
	    $mapping = file_get_contents(DIR_SYSTEM . "/config/stateMapping.json");
	    $mapping = json_decode($mapping, true);

	    if (array_key_exists($country, $mapping) && array_key_exists($state, $mapping[$country])) {
	        return $mapping[$country][$state];
        }

        return $state;
    }
}
