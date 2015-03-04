<?php

namespace Naga\Core\Cli;

use Naga\Core\iComponent;

interface iCommand extends iComponent
{
	public function execute();
	public function name();
	public function aliases();
}