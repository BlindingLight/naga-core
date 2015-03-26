<?php

namespace Naga\Core\Cli\Commands;

use Naga\Core\Cli\iCommand;

final class Update extends Command implements iCommand
{
	protected $_name = 'update';
	protected $_aliases = array('bootstrap-update', 'upb');

	/**
	 * Executes the command.
	 *
	 * @param array $args command arguments
	 * @return bool
	 */
	public function execute(array $args = array())
	{
		$this->cmd()->info('Updating bootstrap...');
		$exec = shell_exec('php ' . getcwd() . '/app/bootstrap.php update');

		if ($exec)
			$this->cmd()->debug($exec);

		$this->cmd()->info('Finished');
	}
}