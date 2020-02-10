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
	const OPENCART_GATEWAY_WIRECARD_VERSION = '1.5.1';
	const OPENCART_GATEWAY_WIRECARD_NAME = 'Wirecard OpenCart Extension';
	const SHOP_NAME = 'OpenCart';

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
	 * Return shop name
	 *
	 * @return string
	 * @since 1.1.0
	 */
	public function getShopName() {
		return self::SHOP_NAME;
	}

	/**
	 * Return version of shop
	 *
	 * @return string
	 * @since 1.1.0
	 */
	public function getShopVersion() {
		return VERSION;
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
