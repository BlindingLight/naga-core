<?php

namespace Naga\Core\Debug;

interface iTimer
{
	/**
	 * Gets timer result in specified time measurement.
	 *
	 * @param   int             $measure    time measure
	 * @param   int             $roundPrecision  round precision
	 * @return  string|float    result
	 */
	public function result($measure = 1, $roundPrecision = 4);

	/**
	 * Starts the timer. Returns with iTimer instance for chainability.
	 *
	 * @return iTimer
	 */
	public function start();

	/**
	 * Stops the timer. Returns with iTimer instance for chainability.
	 *
	 * @return iTimer
	 */
	public function stop();

	/**
	 * Pauses the timer. Returns with iTimer instance for chainability.
	 *
	 * @return iTimer
	 */
	public function pause();

	/**
	 * Resets the timer. Returns with iTimer instance for chainability
	 *
	 * @return iTimer
	 */
	public function reset();

	/**
	 * Gets timer name.
	 *
	 * @return string
	 */
	public function name();

	/**
	 * Gets timer state.
	 *
	 * @return int
	 */
	public function state();
}