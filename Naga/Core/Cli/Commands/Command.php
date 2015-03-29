<?php

namespace Naga\Core\Cli\Commands;

use Naga\Core\Cli\CommandLine;
use Naga\Core\Cli\iCommand;
use Naga\Core\nComponent;

abstract class Command extends nComponent implements iCommand
{
	/**
	 * @var \Naga\Core\Cli\CommandLine
	 */
	protected $_commandLine;
	protected $_name;
	protected $_aliases = array();
	protected $_description = '';

	/**
	 * Sets CommandLine instance.
	 *
	 * @param CommandLine $commandLine
	 */
	public function setCommandLineObject(CommandLine $commandLine)
	{
		$this->_commandLine = $commandLine;
	}

	/**
	 * Executes the command.
	 *
	 * @param array $args command arguments
	 * @return bool
	 */
	abstract public function execute(array $args = array());

	/**
	 * Gets command name.
	 *
	 * @return string
	 */
	public function name()
	{
		return $this->_name;
	}

	/**
	 * Gets command name aliases as array.
	 *
	 * @return array
	 */
	public function aliases()
	{
		return $this->_aliases;
	}

	/**
	 * Gets command description.
	 *
	 * @return string
	 */
	public function description()
	{
		return $this->_description;
	}

	/**
	 * Gets CommandLine instance.
	 *
	 * @return CommandLine
	 */
	protected function cmd()
	{
		return $this->_commandLine;
	}
}