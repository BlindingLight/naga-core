<?php

namespace Naga\Core\Facade;

class Events extends Facade
{
	/**
	 * @see Facade
	 */
	protected static $_accessor = 'events';

	/**
	 * @see \Naga\Core\Event\Events
	 */
	public static function listen($eventName, $task, $params = array(), $priority = 100)
	{
		return self::component()->listen($eventName, $task, $params, $priority);
	}

	/**
	 * @see \Naga\Core\Event\Events
	 */
	public static function subscribe($subscriber)
	{
		return self::component()->subscribe($subscriber);
	}

	/**
	 * @see \Naga\Core\Event\Events
	 */
	public static function fire($eventName, $params = array())
	{
		return self::component()->fire($eventName, $params);
	}

	/**
	 * @see \Naga\Core\Event\Events
	 */
	public static function queue($eventName, $task, $params = array(), $priority = 0)
	{
		return self::component()->queue($eventName, $task, $params, $priority);
	}

	/**
	 * @see \Naga\Core\Event\Events
	 */
	public static function flush($eventName, $params = array())
	{
		return self::component()->flush($eventName, $params);
	}

	/**
	 * @see \Naga\Core\Event\Events
	 */
	public static function registeredEventNames()
	{
		return self::component()->registeredEventNames();
	}
}