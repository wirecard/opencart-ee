<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/opencart-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/opencart-ee/blob/master/LICENSE
 */

include_once(DIR_SYSTEM . 'library/autoload.php');
require_once(dirname( __FILE__ ) . '/wirecard_pg/gateway.php');

/**
 * Class ModelExtensionPaymentWirecardPGSofortbanking
 *
 * Sofortbanking Transaction model
 *
 * @since 1.0.0
 */
class ModelExtensionPaymentWirecardPGSofortbanking extends ModelExtensionPaymentGateway {

	/**
	 * @var string
	 * @since 1.0.0
	 */
	protected $type = 'sofortbanking';

	public function getMethod($address, $total) {
		$language_helper = new ControllerExtensionPaymentWirecardPGLanguageHelper($this->registry);
		$current_language = $language_helper->getActiveLanguageCode();
		$prefix = $this->prefix . $this->type;
		$logo_variant = $this->config->get($prefix . '_logo_variant');
		$url = "https://cdn.klarna.com/1.0/shared/image/generic/badge/{$current_language}/pay_now/{$logo_variant}/pink.svg";

		/*
		 * Since the Klarna CDN URL is extremely long we send it to an URL shortening API.
		 * This is necessary because OpenCart cuts off the payment mehod name after 128 characters.
		 */

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, 'https://is.gd/create.php?format=json&url=' . urlencode($url));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$data = json_decode(curl_exec($ch));
		curl_close($ch);

		$logo = "<img src='" . $data->shorturl . "' width='61' style='margin: 0 18px' />";
		$code = $this->language->get('code');

		if (isset($code) && isset($this->config->get($prefix . '_title' )[$code])) {
			$title = $logo . ' ' . $this->config->get($prefix . '_title' )[$code];
		} else {
			$title = $logo . ' ' . $this->config->get($prefix . '_title' )['en'];
		}

		$method_data = parent::getMethod($address, $total);
		$method_data['title'] = $title;

		return $method_data;
	}
}
