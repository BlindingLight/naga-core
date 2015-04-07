<?php

namespace Naga\Core\Middleware;

use Naga\Core\Application;
use Naga\Core\Event\iSubscriber;
use Naga\Core\nComponent;

abstract class Middleware extends nComponent implements iSubscriber
{
	/**
	 * Construct.
	 *
	 * @param Application $app
	 */
	public function __construct(Application $app)
	{
		$this->registerComponent('application', $app);
		$this->app()->events()->subscribe($this);
	}

	/**
	 * Gets Application instance.
	 *
	 * @return Application
	 * @throws \Naga\Core\Exception\Component\NotFoundException
	 */
	protected function app()
	{
		return $this->component('application');
	}
}