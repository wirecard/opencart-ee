<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/opencart-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/opencart-ee/blob/master/LICENSE
 */

/**
 * Class ExtensionModuleWirecardPGPluginData
 *
 * @since 1.0.0
 */
class ExtensionModuleWirecardPGPluginData {
	const OPENCART_GATEWAY_WIRECARD_VERSION = '1.0.0';
	const OPENCART_GATEWAY_WIRECARD_NAME = 'Wirecard OpenCart Extension';

	/**
	 * Return plugin version
	 *
	 * @return string
	 * @since 1.0.0
	 */
	public function getVersion() {
		return self::OPENCART_GATEWAY_WIRECARD_VERSION;
	}

	/**
	 * Return plugin name
	 *
	 * @return string
	 * @since 1.0.0
	 */
	public function getName() {
		return self::OPENCART_GATEWAY_WIRECARD_NAME;
	}

	/**
	 * Return plugin data to be used in templates
	 *
	 * @return array
	 * @since 1.0.0
	 */
	public function getTemplateData() {
		return array(
			'plugin_name' => self::getName(),
			'plugin_version' => self::getVersion()
		);
	}
}
