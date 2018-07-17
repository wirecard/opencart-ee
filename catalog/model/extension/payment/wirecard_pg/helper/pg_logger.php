<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/opencart-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/opencart-ee/blob/master/LICENSE
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
