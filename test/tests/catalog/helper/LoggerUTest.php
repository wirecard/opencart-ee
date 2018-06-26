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

use Psr\Log\LogLevel;

class LoggerUTest extends \PHPUnit_Framework_TestCase
{
	private $logger;
	private $config;

	public function setUp()
	{
		$this->config = $this->getMockBuilder(Config::class)->disableOriginalConstructor()->getMock();
		$this->logger = new PGLogger($this->config);
	}

	public function testLog()
	{
		$this->logger->log(LogLevel::DEBUG, "Test message");

		$loggedMessage = $this->logger->logger->messages[0];
		$this->stringContains($loggedMessage, 'Test message');
	}

	public function testDebug()
	{
		$this->logger->debug("Test message");

		$loggedMessage = $this->logger->logger->messages[0];
		$this->stringContains($loggedMessage, 'Test message');
		$this->stringContains($loggedMessage, 'DEBUG');
	}

	public function testInfo()
	{
		$this->logger->Info("Test message");

		$loggedMessage = $this->logger->logger->messages[0];
		$this->stringContains($loggedMessage, 'Test message');
		$this->stringContains($loggedMessage, 'INFO');
	}

	public function testNotice()
	{
		$this->logger->notice("Test message");

		$loggedMessage = $this->logger->logger->messages[0];
		$this->stringContains($loggedMessage, 'Test message');
		$this->stringContains($loggedMessage, 'NOTICE');
	}

	public function testWarning()
	{
		$this->logger->warning("Test message");

		$loggedMessage = $this->logger->logger->messages[0];
		$this->stringContains($loggedMessage, 'Test message');
		$this->stringContains($loggedMessage, 'WARNING');
	}

	public function testError()
	{
		$this->logger->error("Test message");

		$loggedMessage = $this->logger->logger->messages[0];
		$this->stringContains($loggedMessage, 'Test message');
		$this->stringContains($loggedMessage, 'ERROR');
	}

	public function testCritical()
	{
		$this->logger->critical("Test message");

		$loggedMessage = $this->logger->logger->messages[0];
		$this->stringContains($loggedMessage, 'Test message');
		$this->stringContains($loggedMessage, 'CRITICAL');
	}

	public function testAlert()
	{
		$this->logger->alert("Test message");

		$loggedMessage = $this->logger->logger->messages[0];
		$this->stringContains($loggedMessage, 'Test message');
		$this->stringContains($loggedMessage, 'ALERT');
	}

	public function testEmergency()
	{
		$this->logger->emergency("Test message");

		$loggedMessage = $this->logger->logger->messages[0];
		$this->stringContains($loggedMessage, 'Test message');
		$this->stringContains($loggedMessage, 'EMERGENCY');
	}
}