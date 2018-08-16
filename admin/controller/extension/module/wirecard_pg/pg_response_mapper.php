<?php
/**
* Shop System Plugins:
* - Terms of Use can be found under:
* https://github.com/wirecard/opencart-ee/blob/master/_TERMS_OF_USE
* - License can be found under:
* https://github.com/wirecard/opencart-ee/blob/master/LICENSE
*/

include_once(DIR_SYSTEM . 'library/autoload.php');

/**
 * Class ControllerExtensionModuleWirecardPGPGResponseMapper
 *
 * Mapping response array
 *
 * @since 1.1.0
 */
class ControllerExtensionModuleWirecardPGPGResponseMapper extends Controller {

	const MERCHANT_ACCOUNT_ID = 'merchant-account-id';
	const TRANSACTION_ID = 'transaction-id';
	const REQUEST_ID = 'request-id';
	const TRANSACTION_TYPE = 'transaction-type';
	const TRANSACTION_STATE = 'transaction-state';
	const COMPLETION_TIME = 'completion-time-stamp';
	const CURRENCY = 'currency';
	const REQUESTED_AMOUNT = 'requested-amount';
	const DESCRIPTOR = 'descriptor';

	const ACCOUNT_HOLDER = 'account-holder.0.';
	const CARD = 'card-token.0.';
	const SHIPPING = 'shipping.0.';
	const ADDRESS = 'address.0.';

	/**
	 * Get transaction details
	 *
	 * @param $response
	 * @return array
	 * @since 1.1.0
	 */
	public function getTransactionDetails($response) {
		$data = array();
		if (isset($response[self::MERCHANT_ACCOUNT_ID])) {
			$data['Merchant Account ID'] = $response[self::MERCHANT_ACCOUNT_ID];
		}
		if (isset($response[self::TRANSACTION_ID])) {
			$data['Transaction ID'] = $response[self::TRANSACTION_ID];
		}
		if (isset($response[self::REQUEST_ID])) {
			$data['Request ID'] = $response[self::REQUEST_ID];
		}
		if (isset($response[self::TRANSACTION_TYPE])) {
			$data['Transaction Type'] = $response[self::TRANSACTION_TYPE];
		}
		if (isset($response[self::TRANSACTION_STATE])) {
			$data['Transaction State'] = $response[self::TRANSACTION_STATE];
		}
		if (isset($response[self::CURRENCY])) {
			$data['Currency'] = $response[self::CURRENCY];
		}
		if (isset($response[self::REQUESTED_AMOUNT])) {
			$data['Requested Amount'] = $response[self::REQUESTED_AMOUNT];
		}
		if (isset($response[self::DESCRIPTOR])) {
			$data['Descriptor'] = $response[self::DESCRIPTOR];
		}
		return $data;
	}

	/**
	 * Get Account holder data
	 *
	 * @param $response
	 * @return array
	 * @since 1.1.0
	 */
	public function getAccountHolder($response) {
		$account_holder = array();
		if (isset($response[self::ACCOUNT_HOLDER . 'first-name'])) {
			$account_holder['Firstname'] = $response[self::ACCOUNT_HOLDER . 'first-name'];
		}
		if (isset($response[self::ACCOUNT_HOLDER . 'last-name'])) {
			$account_holder['Lastname'] = $response[self::ACCOUNT_HOLDER . 'last-name'];
		}
		if (isset($response[self::ACCOUNT_HOLDER . 'email'])) {
			$account_holder['Email'] = $response[self::ACCOUNT_HOLDER . 'email'];
		}
		if (isset($response[self::ACCOUNT_HOLDER . 'date-of-birth'])) {
			$account_holder['Birthdate'] = $response[self::ACCOUNT_HOLDER . 'date-of-birth'];
		}
		if (isset($response[self::ACCOUNT_HOLDER . 'phone'])) {
			$account_holder['Phone'] = $response[self::ACCOUNT_HOLDER . 'phone'];
		}
		if (isset($response[self::ACCOUNT_HOLDER . self::ADDRESS . 'street1'])) {
			$account_holder['Street'] = $response[self::ACCOUNT_HOLDER . self::ADDRESS . 'street1'];
		}
		if (isset($response[self::ACCOUNT_HOLDER . self::ADDRESS . 'city'])) {
			$account_holder['City'] = $response[self::ACCOUNT_HOLDER . self::ADDRESS . 'city'];
		}
		if (isset($response[self::ACCOUNT_HOLDER . self::ADDRESS . 'country'])) {
			$account_holder['Country'] = $response[self::ACCOUNT_HOLDER . self::ADDRESS . 'country'];
		}
		if (isset($response[self::ACCOUNT_HOLDER . self::ADDRESS . 'postal-code'])) {
			$account_holder['Postal Code'] = $response[self::ACCOUNT_HOLDER . self::ADDRESS . 'postal-code'];
		}
		return $account_holder;
	}

	/**
	 * Get shipping holder data
	 *
	 * @param $response
	 * @return array
	 * @since 1.1.0
	 */
	public function getShipping($response) {
		$shipping = array();
		if (isset($response[self::SHIPPING . 'first-name'])) {
			$shipping['Firstname'] = $response[self::SHIPPING . 'first-name'];
		}
		if (isset($response[self::SHIPPING . 'last-name'])) {
			$shipping['Lastname'] = $response[self::SHIPPING . 'last-name'];
		}
		if (isset($response[self::SHIPPING . 'email'])) {
			$shipping['Email'] = $response[self::SHIPPING . 'email'];
		}
		if (isset($response[self::SHIPPING . 'phone'])) {
			$shipping['Phone'] = $response[self::SHIPPING . 'phone'];
		}
		if (isset($response[self::SHIPPING . self::ADDRESS . 'street1'])) {
			$shipping['Street'] = $response[self::SHIPPING . self::ADDRESS . 'street1'];
		}
		if (isset($response[self::SHIPPING . self::ADDRESS . 'city'])) {
			$shipping['City'] = $response[self::SHIPPING . self::ADDRESS . 'city'];
		}
		if (isset($response[self::SHIPPING . self::ADDRESS . 'country'])) {
			$shipping['Country'] = $response[self::SHIPPING . self::ADDRESS . 'country'];
		}
		if (isset($response[self::SHIPPING . self::ADDRESS . 'postal-code'])) {
			$shipping['Postal Code'] = $response[self::SHIPPING . self::ADDRESS . 'postal-code'];
		}
		return $shipping;
	}

	public function getBasicDetails($response) {
		$basic = array();

		if (isset($response['payment-methods.0.name'])) {
			$basic['Payment Method'] = '<img src="' . HTTP_CATALOG .'image/catalog/wirecard_pg_' . $response['payment-methods.0.name'] . '.png" />';
		}
		if (isset($response['completion-time-stamp'])) {
			$basic['Time Stamp'] = $response['completion-time-stamp'];
		}
		if (isset($response['consumer-id'])) {
			$basic['Customer ID'] = $response['consumer-id'];
		}
		if (isset($response['ip-address'])) {
			$basic['IP Address'] = $response['ip-address'];
		}
		if (isset($response['order-number'])) {
			$basic['Order Number'] = $response['order-number'];
		}

		return $basic;
	}


	public function getBasket($response) {
		$basket = array();

	}

	public function getCard($response) {
		$card = array();

		if (isset($response[self::CARD . 'token-id'])) {
			$card['Token'] = $response[self::CARD . 'token-id'];
		}
		if (isset($response[self::CARD . 'masked-account-number'])) {
			$card['Masked PAN'] = $response[self::CARD . 'masked-account-number'];
		}

		return $card;
	}
}
