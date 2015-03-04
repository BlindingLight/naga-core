<?php

namespace Naga\Core\Debug;

use Naga\Core\nComponent;

class Timer extends nComponent implements iTimer
{
	const Stopped = 0;
	const Running = 1;
	const Paused = 2;

	const Stop = 0;
	const Start = 1;
	const Pause = 2;

	const Milliseconds = 1;
	const Seconds = 2;
	const Minutes = 3;
	const Hours = 4;
	const Dynamic = 5;

	private $_name;
	private $_times = array();
	private $_state = self::Stopped;

	/**
	 * Construct.
	 *
	 * @param string $name
	 * @throws \Exception
	 */
	public function __construct($name)
	{
		if (is_array($name) || !(string)$name)
			throw new \Exception('Invalid name specified for Timer: ' . gettype($name));

		$this->_name = (string)$name;
	}

	/**
	 * Gets timer result in specified time measurement. If $measure = Timer::Dynamic, result is returned as
	 * a string with the biggest possible measure that have value bigger or equal to 1.
	 *
	 * @param   int             $measure    time measure
	 * @param   int             $roundPrecision  round precision
	 * @return  string|float    result
	 */
	public function result($measure = self::Dynamic, $roundPrecision = 4)
	{
		$result = 0;
		$currentTime = $this->_times[0]->time;
		foreach ($this->_times as $idx => $time)
		{
			// skipping first timestamp
			if (!$idx)
				continue;

			switch ($time->type)
			{
				case self::Start:
					$currentTime = $time->time;
					break;
				case self::Pause:
					$result += $time->time - $currentTime;
					break;
				case self::Stop:
					$result += $time->time - $currentTime;
					break;
				default:
					break;
			}
		}

		switch ($measure)
		{
			case self::Milliseconds:
				$result = round($result * 1000, $roundPrecision);
				break;
			case self::Seconds:
				break;
			case self::Minutes:
				$result = round($result / 60 / 60, $roundPrecision);
				break;
			case self::Hours:
				$result = round($result / 60 / 60 / 60, $roundPrecision);
				break;
			case self::Dynamic:
				$hours = round($result / 60 / 60, $roundPrecision);
				$minutes = round($result / 60, $roundPrecision);
				if ($hours >= 1)
				{
					$result = "{$hours}h";
					break;
				}
				if ($minutes >= 1)
				{
					$result = "{$minutes}m";
					break;
				}
				// seconds
				if ($result >= 1)
				{
					$result = round($result, $roundPrecision) . 's';
					break;
				}

				$result = round($result * 1000, $roundPrecision) . 'ms';
				break;
			default:
				break;
		}
		return $result;
	}

	/**
	 * Starts the timer. Returns with iTimer instance for chainability.
	 *
	 * @return iTimer
	 */
	public function start()
	{
		if ($this->_state == self::Running)
			return $this;

		$this->createTimeEntry(self::Start);
		$this->_state = self::Running;

		return $this;
	}

	/**
	 * Stops the timer. Returns with iTimer instance for chainability.
	 *
	 * @return iTimer
	 */
	public function stop()
	{
		if ($this->_state == self::Stopped)
			return $this;

		$this->createTimeEntry(self::Stop);
		$this->_state = self::Stopped;

		return $this;
	}

	/**
	 * Pauses the timer. Returns with iTimer instance for chainability.
	 *
	 * @return iTimer
	 */
	public function pause()
	{
		if ($this->_state != self::Running)
			return $this;

		$this->createTimeEntry(self::Pause);
		$this->_state = self::Paused;

		return $this;
	}

	/**
	 * Gets timer name.
	 *
	 * @return string
	 */
	public function name()
	{
		return $this->_name;
	}

	/**
	 * Gets timer state.
	 *
	 * @return int
	 */
	public function state()
	{
		return $this->_state;
	}

	/**
	 * Creates a time entry that contains current timestamp and type.
	 *
	 * @param int $type
	 * @return Profiler
	 * @throws \Exception
	 */
	protected function createTimeEntry($type)
	{
		if ($type != self::Start && $type != self::Stop && $type != self::Pause)
			throw new \Exception("Invalid Timer time entry type specified: {$type}");

		$this->_times[] = (object)array(
			'type' => $type,
			'time' => microtime(true)
		);

		return $this;
	}

	/**
	 * Sets current state of Profiler instance.
	 *
	 * @param int $state
	 * @return Profiler
	 * @throws \Exception
	 */
	protected function setState($state)
	{
		if ($state != self::Running && $state != self::Stopped && $state != self::Paused)
			throw new \Exception("Invalid Timer state specified: {$state}");

		$this->_state = (int)$state;

		return $this;
	}

	/**
	 * Resets the timer. Returns with iTimer instance for chainability
	 *
	 * @return iTimer
	 */
	public function reset()
	{
		$this->_state = self::Stopped;
		$this->_times = array();
	}
}