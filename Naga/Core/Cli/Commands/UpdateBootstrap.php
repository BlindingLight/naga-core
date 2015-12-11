<?php

namespace Naga\Core\Cli\Commands;

use Naga\Core\Cli\iCommand;

final class UpdateBootstrap extends Command implements iCommand
{
	protected $_name = 'update-bootstrap';
	protected $_aliases = ['updb', 'regenerate-bootstrap', 'regenb'];
	protected $_description = 'Updates bootstrap code. You can minify the generated code by writing "true" or "minify" after updb command.';

	/**
	 * Executes the command.
	 *
	 * @param array $args command arguments
	 * @return bool
	 */
	public function execute(array $args = array())
	{
		$this->cmd()->info('Updating bootstrap...');
		shell_exec('php ' . getcwd() . '/app/bootstrap.php ' . implode(' ', $args));

		$this->cmd()->info('UpdateBootstrap finished.');
	}
}