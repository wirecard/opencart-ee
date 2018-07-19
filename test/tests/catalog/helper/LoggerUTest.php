<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/opencart-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/opencart-ee/blob/master/LICENSE
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