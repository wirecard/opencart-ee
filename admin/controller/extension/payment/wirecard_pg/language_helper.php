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
				if (is_array($this->config->get($prefix . $field)) &&
					array_key_exists($code, $this->config->get($prefix . $field))) {
					$keys[$field][$code] = $this->config->get($prefix . $field)[$code];
				} else {
					$keys[$field][$code] = $default[$field];
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
