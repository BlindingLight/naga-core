<?php

namespace Naga\Core\Debug;

use Naga\Core\Debug\Log\iLogger;
use Naga\Core\Debug\Log\JsConsoleLogger;
use Naga\Core\nComponent;

class Profiler extends nComponent implements iProfiler
{
	/**
	 * @var bool enable profiling globally
	 */
	private static $_enabledGlobally = false;

	/**
	 * @var bool enable profiling per instance
	 */
	private $_enabled = true;

	/**
	 * @var string Profiler instance name
	 */
	private $_name;

	/**
	 * @var Timer[] Timer instances
	 */
	private $_timers = array();

	/**
	 * Construct.
	 *
	 * @param   string  $name
	 * @param   iLogger $logger
	 * @throws  \Exception
	 */
	public function __construct($name, iLogger $logger = null)
	{
		if (is_array($name) || !(string)$name)
			throw new \Exception('Invalid name specified for Profiler instance: ' . gettype($name));

		$this->_name = (string)$name;

		if ($logger instanceof iLogger)
			$this->setLogger($logger);
		else
			$this->setLogger(new JsConsoleLogger());
	}

	/**
	 * @see iProfiler
	 */
	public function createTimer($name, $start = true, $overwrite = true)
	{
		if (!$this->_enabled)
			return $this;

		if (isset($this->_timers[$name]))
		{
			if ($overwrite)
				$this->_timers[$name]->reset();
			else
				trigger_error("Created timer overwrites existing: {$name}", E_USER_NOTICE);
		}
		else
			$this->_timers[$name] = new Timer($name);

		if ($start)
			$this->_timers[$name]->start();

		return $this;
	}

	/**
	 * @see iProfiler
	 */
	public function startTimer($name)
	{
		if ($this->_enabled)
			$this->timer($name)->start();

		return $this;
	}

	/**
	 * @see iProfiler
	 */
	public function pauseTimer($name)
	{
		if ($this->_enabled)
			$this->timer($name)->pause();

		return $this;
	}

	/**
	 * @see iProfiler
	 */
	public function stopTimer($name)
	{
		if ($this->_enabled)
			$this->timer($name)->stop();

		return $this;
	}

	/**
	 * Gets a Timer instance with the specified name.
	 *
	 * @param   string  $name
	 * @return  iTimer
	 * @throws  \Exception
	 */
	protected function timer($name)
	{
		if (!isset($this->_timers[$name]))
			throw new \Exception("Can't get iTimer with name {$name}");

		return $this->_timers[$name];
	}

	/**
	 * @see iProfiler
	 */
	public function timers()
	{
		return $this->_timers;
	}

	/**
	 * @see iProfiler
	 */
	public function timerResults($measure = Timer::Dynamic, $roundPrecision = 4)
	{
		if (!$this->_enabled)
			return array();

		$results = array();
		foreach ($this->_timers as $timer)
			$results[$timer->name()] = $timer->result($measure, $roundPrecision);

		return $results;
	}

	/**
	 * @see iProfiler
	 */
	public function timerResult($name, $measure = Timer::Dynamic, $roundPrecision = 4)
	{
		if (!$this->_enabled)
			return null;

		return $this->timer($name)->result($measure, $roundPrecision);
	}

	/**
	 * @see iProfiler
	 */
	public function dispatchLog()
	{
		if (!$this->_enabled)
			return $this;

		foreach ($this->timerResults() as $timerName => $result)
		{
			$this->logger()->debug("{$timerName}: {$result}");
		}

		$this->logger()->dispatch();

		return $this;
	}

	/**
	 * @see iProfiler
	 */
	public function generateLog()
	{
		if (!self::$_enabledGlobally || !$this->_enabled)
			return '';

		foreach ($this->timerResults() as $timerName => $result)
		{
			$this->logger()->debug("{$timerName}: {$result}");
		}

		return $this->logger()->generate();
	}

	/**
	 * Sets iLogger instance.
	 *
	 * @param iLogger $logger
	 */
	public function setLogger(iLogger $logger)
	{
		$logger->group('Profiler ' . str_replace('\\', '.', $this->name()));
		$this->registerComponent('logger', $logger);
	}

	/**
	 * Gets iLogger instance.
	 *
	 * @return iLogger
	 * @throws \RuntimeException
	 */
	public function logger()
	{
		try
		{
			return $this->component('logger');
		}
		catch (\Exception $e)
		{
			throw new \RuntimeException("Can't get iLogger instance of profiler {$this->name()}.");
		}
	}

	/**
	 * Gets profiler instance name.
	 *
	 * @return string
	 */
	public function name()
	{
		return $this->_name;
	}

	/**
	 * @see iProfiler
	 */
	public static function enableGlobally()
	{
		self::$_enabledGlobally = true;
	}

	/**
	 * @see iProfiler
	 */
	public static function disableGlobally()
	{
		self::$_enabledGlobally = false;
	}

	/**
	 * @see iProfiler
	 */
	public function enable()
	{
		$this->_enabled = true;
	}

	/**
	 * @see iProfiler
	 */
	public function disable()
	{
		$this->_enabled = false;
	}
}