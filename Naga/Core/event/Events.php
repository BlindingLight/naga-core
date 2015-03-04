<?php

namespace Naga\Core\Event;

use Naga\Core\nComponent;

/**
 * Provides a simple observer implementation.
 *
 * @package Naga\Core\Event
 * @author  BlindingLight<bloodredshade@gmail.com>
 */
class Events extends nComponent
{
	/**
	 * @var Event[] events
	 */
	private $_events = array();

	/**
	 * Construct.
	 */
	public function __construct()
	{
	}

	/**
	 * Adds a listener that can have low priority (below 100).
	 *
	 * @param string            $eventName
	 * @param callable|string   $task
	 * @param array|null        $params
	 * @param int               $priority
	 * @param bool              $queued tells whether $task is queued
	 * @return $this
	 */
	protected function lowPriorityListen($eventName, $task, $params = array(), $priority = 1, $queued = false)
	{
		// handling wildcard
		$events = array($eventName);
		if (strpos($eventName, '*') !== false)
		{
			$matches = array();
			preg_match('/[^\*]+/', $eventName, $matches);

			$events = array();
			if (count($matches) && $matches[0])
			{
				$match = $matches[0];
				foreach ($this->_events as $name => $event)
				{
					if (strpos($name, $match) === 0)
						$events[] = $name;
				}
			}
		}

		foreach ($events as $eventName)
		{
			if (!isset($this->_events[$eventName]))
				$this->_events[$eventName] = new Event($eventName);

			if (!$queued)
				$this->_events[$eventName]->addListener($task, $params, $priority);
			else
				$this->_events[$eventName]->addToQueue($task, $params, $priority);
		}

		return $this;
	}

	/**
	 * Adds a listener. Priority must be equal or greater than 100, priorities below 100 are reserved
	 * for core listeners.
	 *
	 * @param string            $eventName
	 * @param callable|string   $task
	 * @param array|null        $params
	 * @param int               $priority
	 * @return $this
	 */
	public function listen($eventName, $task, $params = array(), $priority = 100)
	{
		if ($priority < 100)
			$priority += 100;

		return $this->lowPriorityListen($eventName, $task, $params, $priority);
	}

	/**
	 * Calls the passed class' subscribe($event) method. Usable for subscribing to multiple
	 * events within a method. Subscribe method can also be static.
	 *
	 * @param object|string $subscriber
	 * @return $this
	 */
	public function subscribe($subscriber)
	{
		call_user_func_array(array($subscriber, 'subscribe'), array($this));

		return $this;
	}

	/**
	 * Fires an event.
	 *
	 * @param string $eventName
	 * @param array  $params
	 * @return $this
	 * @throws \RuntimeException
	 */
	public function fire($eventName, $params = array())
	{
		if (isset($this->_events[$eventName]))
			$this->_events[$eventName]->fire($params);

		return $this;
	}

	/**
	 * Adds an item to the event queue. Use flush() to fire all items in the queue.
	 *
	 * @param string            $eventName
	 * @param callable|string   $task
	 * @param array|null        $params
	 * @param int               $priority
	 * @return $this
	 */
	public function queue($eventName, $task, $params = array(), $priority = 0)
	{
		$this->lowPriorityListen($eventName, $task, $params, $priority, true);

		return $this;
	}

	/**
	 * Executes items in the event's queue and clears the queue.
	 *
	 * @param string $eventName
	 * @param array $params
	 */
	public function flush($eventName, $params = array())
	{
		if (isset($this->_events[$eventName]))
			$this->_events[$eventName]->flush($params);
	}

	/**
	 * Gets registered event names.
	 *
	 * @return array
	 */
	public function registeredEventNames()
	{
		return array_keys($this->_events);
	}
}