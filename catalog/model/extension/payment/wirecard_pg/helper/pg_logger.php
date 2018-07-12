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

include_once(DIR_SYSTEM . 'library/autoload.php');

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

/**
 * Class PGLogger
 *
 * PSR-3 compatible logging implementation for OpenCart
 *
 * @since 1.0.0
 */
class PGLogger implements LoggerInterface
{
	/**
	 * @var Log
	 * @since 1.0.0
	 */
	public $logger;

	/**
	 * Logger constructor.
	 *
	 * @param Config $config
	 * @since 1.0.0
	 */
	public function __construct($config) {
		$this->logger = new Log($config->get('config_error_filename'));
	}

	/**
	 * Log emergencies
	 *
	 * @param string $message
	 * @param array $context
	 * @since 1.0.0
	 */
	public function emergency($message, array $context = array()) {
		$this->log(LogLevel::EMERGENCY, $message, $context);
	}

	/**
	 * Log alerts
	 *
	 * @param string $message
	 * @param array $context
	 * @since 1.0.0
	 */
	public function alert($message, array $context = array()) {
		$this->log(LogLevel::ALERT, $message, $context);
	}

	/**
	 * Log critical errors
	 *
	 * @param string $message
	 * @param array $context
	 * @since 1.0.0
	 */
	public function critical($message, array $context = array()) {
		$this->log(LogLevel::CRITICAL, $message, $context);
	}

	/**
	 * Log errors
	 *
	 * @param string $message
	 * @param array $context
	 * @since 1.0.0
	 */
	public function error($message, array $context = array()) {
		$this->log(LogLevel::ERROR, $message, $context);
	}

	/**
	 * Log warnings
	 *
	 * @param string $message
	 * @param array $context
	 * @since 1.0.0
	 */
	public function warning($message, array $context = array()) {
		$this->log(LogLevel::WARNING, $message, $context);
	}

	/**
	 * Log notices
	 *
	 * @param string $message
	 * @param array $context
	 * @since 1.0.0
	 */
	public function notice($message, array $context = array()) {
		$this->log(LogLevel::NOTICE, $message, $context);
	}

	/**
	 * Log information
	 *
	 * @param string $message
	 * @param array $context
	 * @since 1.0.0
	 */
	public function info($message, array $context = array()) {
		$this->log(LogLevel::INFO, $message, $context);
	}

	/**
	 * Log debug messages
	 *
	 * @param string $message
	 * @param array $context
	 * @since 1.0.0
	 */
	public function debug($message, array $context = array()) {
		$this->log(LogLevel::DEBUG, $message, $context);
	}

	/**
	 * Log a message of any level
	 *
	 * @param mixed $level
	 * @param string $message
	 * @param array $context
	 * @since 1.0.0
	 */
	public function log($level, $message, array $context = array()) {
		$level_name = strtoupper($level);
		$this->logger->write("$level_name: $message");
	}

}
