<?php

namespace Naga\Core\Debug;

use Naga\Core\Debug\Log\iLogger;

interface iProfiler
{
	/**
	 * Enables profiling with iProfiler globally.
	 */
	public static function enableGlobally();

	/**
	 * Disables profiling with iProfiler globally.
	 */
	public static function disableGlobally();

	/**
	 * Enables profiling with iProfiler.
	 */
	public function enable();

	/**
	 * Disables profiling with iProfiler.
	 */
	public function disable();

	/**
	 * Creates a timer with the specified name. Also starts it if $start = true.
	 * If there is an existing timer with $name and $overwrite = true, resets it silently,
	 * else triggers an E_USER_NOTICE level error.
	 *
	 * @param   string  $name
	 * @param   bool    $start start the timer?
	 * @param   bool    $overwrite overwrite existing timer?
	 * @return  $this
	 */
	public function createTimer($name, $start = true, $overwrite = true);

	/**
	 * Starts a timer.
	 *
	 * @param string $name
	 * @return $this
	 */
	public function startTimer($name);

	/**
	 * Pause a timer.
	 *
	 * @param string $name
	 * @return $this
	 */
	public function pauseTimer($name);

	/**
	 * Stops a timer.
	 *
	 * @param string $name
	 * @return $this
	 */
	public function stopTimer($name);

	/**
	 * Gets all iTimer instances in an array.
	 *
	 * @return iTimer[]
	 */
	public function timers();

	/**
	 * Gets all iTimer results in an array.
	 *
	 * @param int $measure
	 * @param int $roundPrecision
	 * @return array
	 */
	public function timerResults($measure = 1, $roundPrecision = 4);

	/**
	 * Gets timer result in specified time measurement.
	 *
	 * @param   string          $name           timer name
	 * @param   int             $measure        time measure
	 * @param   int             $roundPrecision round precision
	 * @return  string|float    result
	 */
	public function timerResult($name, $measure = Timer::Dynamic, $roundPrecision = 4);

	/**
	 * Outputs generated log from timer results.
	 */
	public function dispatchLog();

	/**
	 * Gets generated log from timer results.
	 *
	 * @return string
	 */
	public function generateLog();

	/**
	 * Sets iLogger instance.
	 *
	 * @param iLogger $logger
	 */
	public function setLogger(iLogger $logger);

	/**
	 * Gets profiler instance name.
	 *
	 * @return string
	 */
	public function name();
}