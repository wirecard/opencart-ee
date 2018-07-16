<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/opencart-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/opencart-ee/blob/master/LICENSE
 */

/**
 * Class ControllerExtensionPaymentWirecardPGLanguageHelper
 *
 * LanguageHelper controller
 *
 * @since 1.0.0
 */
class ControllerExtensionPaymentWirecardPGLanguageHelper extends Controller {

	/**
	 * Get config fields depending on the lang in the shop if no config is set the def value will be taken
	 *
	 * @param array $fields
	 * @param string $prefix
	 * @param string $type
	 * @param array $default
	 * @return array
	 */
	public function getConfigFields($fields, $prefix, $type, $default) {
		$prefix = $prefix . $type . '_';
		$keys = [];
		foreach ($fields as $field) {
			foreach ($this->getAllLanguagesCodes() as $code) {
				$keys[$field][$code] = $default[$field];
				if (is_array($this->config->get($prefix . $field)) &&
					array_key_exists($code, $this->config->get($prefix . $field))) {
					$keys[$field][$code] = $this->config->get($prefix . $field)[$code];
				}
			}
		}
		return $keys;
	}

	/**
	 * Get shop language codes
	 *
	 * @return array
	 */
	private function getAllLanguagesCodes() {
		$this->load->model('localisation/language');

		$data = [];
		foreach ($this->model_localisation_language->getLanguages() as $language) {
			array_push($data, preg_split('/[-_]/', $language['code'])[0]);
		}
		return $data;
	}
}
