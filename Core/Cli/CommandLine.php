<?php

namespace Naga\Core\Cli;

use Naga\Core\Config\ConfigBag;
use Naga\Core\Debug\Log\CommandLineLogger;
use Naga\Core\Debug\Log\iLogger;
use Naga\Core\nComponent;

class CommandLine extends nComponent
{
	protected $_commands = array();
	protected $_aliases = array();

	protected $_settings;
	protected $_logger;

	public function __construct(ConfigBag $settings, iLogger $logger = null)
	{
		$this->_logger = $logger ? $logger : new CommandLineLogger();
		$commands = $settings->offsetExists('commands') ? $settings->getArray('commands') : array();
		$settings->remove('commands');

		foreach ($commands as $command)
		{
			try
			{
				$this->registerCommand($command);
			}
			catch (\Exception $e)
			{
			}
		}

		$this->_settings = $settings;
	}

	/**
	 * @see CommandLineLogger
	 */
	public function log($severity, $message)
	{
		$this->_logger->log($severity, $this->getSeverityPrefix($severity) . $message);
	}

	/**
	 * @see CommandLineLogger
	 */
	public function debug($message)
	{
		$this->_logger->debug($this->getSeverityPrefix(iLogger::Debug) . $message);
	}

	/**
	 * @see CommandLineLogger
	 */
	public function info($message)
	{
		$this->_logger->info($this->getSeverityPrefix(iLogger::Info) . $message);
	}

	/**
	 * @see CommandLineLogger
	 */
	public function notice($message)
	{
		$this->_logger->notice($this->getSeverityPrefix(iLogger::Notice) . $message);
	}

	/**
	 * @see CommandLineLogger
	 */
	public function warning($message)
	{
		$this->_logger->warning($this->getSeverityPrefix(iLogger::Warning) . $message);
	}

	/**
	 * @see CommandLineLogger
	 */
	public function error($message)
	{
		$this->_logger->error($this->getSeverityPrefix(iLogger::Error) . $message);
	}

	/**
	 * @see CommandLineLogger
	 */
	public function critical($message)
	{
		$this->_logger->critical($this->getSeverityPrefix(iLogger::Critical) . $message);
	}

	/**
	 * @see CommandLineLogger
	 */
	public function alert($message)
	{
		$this->_logger->alert($this->getSeverityPrefix(iLogger::Alert) . $message);
	}

	/**
	 * @see CommandLineLogger
	 */
	public function emergency($message)
	{
		$this->_logger->emergency($this->getSeverityPrefix(iLogger::Emergency) . $message);
	}

	protected function getSeverityPrefix($severity)
	{
		if (!$this->config()->getBool('severityPrefixesEnabled'))
			return '';

		$severityPrefixes = &$this->config()->getArray('severityPrefixes');

		$prefix = isset($severityPrefixes[$severity]) ? $severityPrefixes[$severity] : '';
		if ($this->config()->getBool('colorsEnabled'))
		{
			$colors = &$this->config()->getArray('severityPrefixColors');
			$color = isset($colors[$severity]) ? $colors[$severity] : iLogger::White;

			$prefix = "\033[{$color}m{$prefix}\033[0m";
		}

		return $prefix;
	}

	public function ask($question)
	{

	}

	public function executeCommand($name)
	{

	}

	public function registerCommand(iCommand $command)
	{

	}

	public function removeCommand()
	{

	}

	/**
	 * Gets ConfigBag instance.
	 *
	 * @return ConfigBag
	 */
	protected function config()
	{
		return $this->_settings;
	}
}