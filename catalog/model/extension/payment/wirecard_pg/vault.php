<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/opencart-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/opencart-ee/blob/master/LICENSE
 */

/**
 * Class ModelExtensionPaymentWirecardPGVault
 *
 * @since 1.1.0
 */
class ModelExtensionPaymentWirecardPGVault extends Model {

	const VAULT_TABLE = 'wirecard_ee_vault';

	/**
	 * Get all Credit Cards associated with a user.
	 *
	 * @return array
	 * @since 1.1.0
	 */
	public function getCards() {
		$address_id = $this->session->data['payment_address']['address_id'];
		if (isset($this->session->data['shipping_address']['address_id'])) {
			$address_id = $this->session->data['shipping_address']['address_id'];
		}
		$cards = $this->db->query(
			"SELECT * FROM `" . DB_PREFIX . self::VAULT_TABLE . "` 
			WHERE user_id=" . $this->customer->getId() . "
			AND address_id=" . $address_id . "
			ORDER BY vault_id DESC"
		)->rows;

		return array_filter($cards, function($card) {
			$date_expiration = new DateTime($card['expiration_year'] . '-' . $card['expiration_month'] . '-01');
			$date_expiration->add(new DateInterval('P6M'));

			$date_today = new DateTime();

			return $date_today < $date_expiration;
		});
	}



	/**
	 * Save a Credit Card to the database.
	 *
	 * @param Wirecard\PaymentSdk\Response\SuccessResponse $response
	 * @param array card
	 * @since 1.1.0
	 */
	public function saveCard($response, $card) {
		$token = $response->getCardTokenId();
		$masked_pan = $response->getMaskedAccountNumber();
		$expiration_month = $card['expiration-month'];
		$expiration_year = $card['expiration-year'];
		if (!isset($this->session->data['shipping_address']['address_id'])) {
			return;
		}
		$address_id = $this->session->data['shipping_address']['address_id'];

		$existing_cards = $this->getCards();
		if(!empty($existing_cards)) {
			foreach($existing_cards as $card) {
				if ($card['token'] == $token && $card['address_id'] == $address_id) {
					return;
				}
			}
		}

		$this->db->query(
			"INSERT INTO `" . DB_PREFIX . self::VAULT_TABLE . "` SET
			`user_id` = " . $this->customer->getId() . ",
			`address_id` = " . $address_id . ",
			`token` = '" . $this->db->escape($token) . "',
			`masked_pan` = '" . $this->db->escape($masked_pan) . "',
			`expiration_month` = " . $expiration_month . ",
			`expiration_year` = " . $expiration_year . ";"
		);
	}

	/**
	 * Delete a Credit Card from the database.
	 *
	 * @param $card_id
	 * @return bool
	 * @since 1.1.0
	 */
	public function deleteCard($card_id) {
		return $this->db->query(
			"DELETE FROM `" . DB_PREFIX . self::VAULT_TABLE . "` 
			WHERE user_id=" . $this->customer->getId() . "
			AND vault_id=" . $this->db->escape($card_id) . ";"
		);
	}
}
