<?php

namespace Naga\Core\Event;

use Naga\Core\iComponent;

/**
 * Interface for creating Event subscriber classes.
 *
 * @package Naga\Core\Event
 * @author  BlindingLight<bloodredshade@gmail.com>
 */
interface iSubscriber extends iComponent
{
	/**
	 * Subscribes to events.
	 */
	public function subscribe();
}