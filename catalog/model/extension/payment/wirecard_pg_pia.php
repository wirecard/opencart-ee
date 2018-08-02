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
 * Class ModelExtensionPaymentWirecardPGPia
 *
 * Payment In Advance Transaction model
 *
 * @since 1.1.0
 */
class ModelExtensionPaymentWirecardPGPia extends ModelExtensionPaymentGateway {

	/**
	 * @var string
	 * @since 1.1.0
	 */
	protected $type = 'pia';

	/**
	 * Handle the form interaction response in an appropriate way.
	 *
	 * @param \Wirecard\PaymentSdk\Response\FormInteractionResponse $response
	 * @return mixed
	 */
	public function handleFormInteractionResponse($response) {
		$form_fields = $response->getFormFields();
		$response_query = array();

		foreach ($form_fields->getIterator() as $key => $value) {
			$response_query[$key] = $value;
		}

		$query = http_build_query($response_query);
		return $response->getUrl() . '&' . $query;
	}
}
