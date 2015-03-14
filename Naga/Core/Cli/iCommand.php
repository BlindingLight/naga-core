<?php

namespace Naga\Core\Cli;

use Naga\Core\iComponent;

interface iCommand extends iComponent
{
	/**
	 * Executes the command.
	 *
	 * @param array $args command arguments
	 * @return bool
	 */
	public function execute(array $args = array());

	/**
	 * Gets command name.
	 *
	 * @return string
	 */
	public function name();

	/**
	 * Gets command name aliases as array.
	 *
	 * @return array
	 */
	public function aliases();
}