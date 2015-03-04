<?php

namespace Naga\Core\Event;

use Naga\Core\nComponent;

/**
 * Event implementation. When an event fires, the callback gets parameters in this order:
 * 1. Event name
 * 2. Parameters passed to listen() or queue()
 * 3. Parameters passed when event fired / queue flushed
 *
 * @package Naga\Core\Event
 * @author  BlindingLight<bloodredshade@gmail.com>
 */
class Event extends nComponent
{
	/**
	 * @var string event name
	 */
	private $_name;
	/**
	 * @var array event listeners
	 */
	private $_listeners = array();

	/**
	 * @var array queued listeners
	 */
	private $_queue = array();

	/**
	 * Construct.
	 *
	 * @param string $name
	 */
	public function __construct($name)
	{
		$this->_name = $name;
	}

	/**
	 * Adds a listener.
	 *
	 * @param callable|string   $task
	 * @param array|null        $params
	 * @param int               $priority
	 * @return $this
	 */
	public function addListener($task, $params, $priority)
	{
		if (!isset($this->_listeners[$priority]) || !is_array($this->_listeners[$priority]))
			$this->_listeners[$priority] = array();

		$this->_listeners[$priority][] = (object)array(
			'task' => $task,
			'params' => is_array($params) ? $params : array(),
		);

		return $this;
	}

	/**
	 * Adds a listener to the event queue
	 *
	 * @param callable|string   $task
	 * @param array|null        $params
	 * @param int               $priority
	 * @return $this
	 */
	public function addToQueue($task, $params, $priority)
	{
		if (!isset($this->_queue[$priority]) || !is_array($this->_queue[$priority]))
			$this->_queue[$priority] = array();

		$this->_queue[$priority][] = (object)array(
			'task' => $task,
			'params' => is_array($params) ? $params : array(),
		);

		return $this;
	}

	/**
	 * Fires the event.
	 *
	 * @param array $params
	 * @param bool  $queue tells whether we process the event queue
	 * @return $this
	 */
	public function fire($params = array(), $queue = false)
	{
		if (!$queue)
			$listeners = &$this->_listeners;
		else
			$listeners = &$this->_queue;

		foreach ($listeners as $priority => $items)
		{
			$stopPropagation = false;
			foreach ($items as $listener)
			{
				$privateParams = array($this->_name);
				$privateParams = array_merge($privateParams, $listener->params, $params);
				if (is_callable($listener->task))
				{
					$callable = $listener->task;
					$stopPropagation = call_user_func_array($callable, $privateParams);
				}
				else if (is_string($listener->task))
				{
					if (strpos($listener->task, '@') !== false)
						$callable = explode('@', $listener->task);
					else
						$callable = array($listener->task, 'handle');

					foreach ($listener->params as $param)
					{
						if ($param instanceof $callable[0])
							$callable[0] = $param;
					}

					$stopPropagation = call_user_func_array($callable, $privateParams);
				}

				if ($stopPropagation === false)
					break;
			}

			if ($stopPropagation === false)
				break;
		}

		return $this;
	}

	/**
	 * Executes items in the event queue and clears the queue.
	 *
	 * @param array $params
	 * @return $this
	 */
	public function flush($params)
	{
		$this->fire($params, true);
		$this->_queue = array();

		return $this;
	}

	/**
	 * Gets event name.
	 *
	 * @return string
	 */
	public function name()
	{
		return $this->_name;
	}
}