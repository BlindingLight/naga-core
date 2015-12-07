<?php

namespace Naga\Core\Cli;

use Naga\Core\Config\ConfigBag;
use Naga\Core\Debug\Log\CommandLineLogger;
use Naga\Core\Debug\Log\iLogger;
use Naga\Core\nComponent;

class CommandLine extends nComponent
{
	/**
	 * @var \Naga\Core\Cli\Commands\Command[] commands
	 */
	protected $_commands = [];
	/**
	 * @var array command aliases
	 */
	protected $_aliases = [];
	/**
	 * @var ConfigBag
	 */
	protected $_settings;
	/**
	 * @var iLogger
	 */
	protected $_logger;

	public function __construct(ConfigBag $settings, iLogger $logger = null)
	{
		$this->_logger = $logger ? $logger : new CommandLineLogger();
		$commands = $settings->offsetExists('commands') ? $settings->getArray('commands') : [];
		$settings->remove('commands');

		foreach ($commands as $command)
		{
			try
			{
				$command->setCommandLineObject($this);
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

		$severityPrefixes = $this->config()->getArray('severityPrefixes');

		$prefix = isset($severityPrefixes[$severity]) ? $severityPrefixes[$severity] : '';
		if ($this->config()->getBool('colorsEnabled'))
		{
			$colors = $this->config()->getArray('severityPrefixColors');
			$color = isset($colors[$severity]) ? $colors[$severity] : iLogger::White;

			$prefix = "\033[{$color}m{$prefix}\033[0m";
		}

		return $prefix;
	}

	/**
	 * Asks a question.
	 *
	 * @param string $question
	 * @return string
	 */
	public function ask($question)
	{
		$this->log(-1, '[?] ' . $question);
		$handle = fopen ('php://stdin', 'r');
		$line = fgets($handle);

		return trim($line);
	}

	/**
	 * Executes a command.
	 *
	 * @param string $name
	 * @param array $args
	 * @return bool
	 */
	public function executeCommand($name, $args = array())
	{
		$command = isset($this->_commands[$name]) ? $this->_commands[$name] : null;
		if (!$command)
		{
			$command = isset($this->_aliases[$name]) && isset($this->_commands[$this->_aliases[$name]])
				? $this->_commands[$this->_aliases[$name]]
				: null;
		}

		if (!$command)
		{
			$this->error("Command with name {$name} not found.");
			return false;
		}

		return $command->execute($args);
	}

	/**
	 * Registers a command.
	 *
	 * @param iCommand $command
	 */
	public function registerCommand(iCommand $command)
	{
		// registering commands
		$this->_commands[$command->name()] = $command;

		// registering aliases
		foreach ($command->aliases() as $name)
		{
			// we won't register an alias if there is a command with that name
			if (isset($this->_commands[$name]))
				continue;

			$this->_aliases[$name] = $command->name();
		}
	}

	/**
	 * Removes a command.
	 *
	 * @param string $commandName
	 */
	public function removeCommand($commandName)
	{
		if (isset($this->_commands[$commandName]))
			unset($this->_commands[$commandName]);
	}

	/**
	 * Dumps available commands to console.
	 */
	public function dumpCommands()
	{
		$text = 'Available commands:';
		foreach ($this->_commands as $command)
			$text .= "\n\t" . $command->name() . "\t\t" . $command->description();

		$this->info($text);
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