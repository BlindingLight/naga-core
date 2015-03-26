<?php

namespace Naga\Core\Debug\Log;

use Naga\Core\iComponent;

interface iLogger extends iComponent
{
	// debug-level messages
	const Debug = 7;
	// informational messages
	const Info = 6;
	// normal but significant condition
	const Notice = 5;
	// warning conditions
	const Warning = 4;
	// error conditions
	const Error = 3;
	// critical conditions
	const Critical = 2;
	// action must be taken immediately
	const Alert = 1;
	// system is unusable
	const Emergency = 0;
	
	// console colors
	const Black = '0;30';
	const DarkGrey = '0;30';
	const Blue = '0;34';
	const LightBlue = '0;34';
	const Green = '1;32';
	const LightGreen = '1;32';
	const Cyan = '0;36';
	const LightCyan = '0;36';
	const Red = '1;31';
	const LightRed = '0;31';
	const Purple = '1;35';
	const LightPurple = '0;35';
	const Brown = '1;33';
	const Yellow = '0;33';
	const LightGrey = '0;37';
	const White = '0;37';

	/**
	 * Generates output from stored log messages.
	 *
	 * @return string
	 */
	public function generate();

	/**
	 * Outputs generated log messages.
	 *
	 * @return $this
	 */
	public function dispatch();

	/**
	 * Creates and sets a log group. Every message before another group() call
	 * will added to this group.
	 * You can nest groups by separating subgroups
	 * with ., eg: application.database.queries.
	 *
	 * @param string $name
	 * @return $this
	 */
	public function group($name);

	/**
	 * Logs a message.
	 *
	 * @param int $severity
	 * @param string $message
	 * @return $this
	 */
	public function log($severity, $message);

	/**
	 * Logs a debug message.
	 *
	 * @param string $message
	 * @return $this
	 */
	public function debug($message);

	/**
	 * Logs an info message.
	 *
	 * @param string $message
	 * @return $this
	 */
	public function info($message);

	/**
	 * Logs a notice message.
	 *
	 * @param string $message
	 * @return $this
	 */
	public function notice($message);

	/**
	 * Logs a warning message.
	 *
	 * @param string $message
	 * @return $this
	 */
	public function warning($message);

	/**
	 * Logs an error message.
	 *
	 * @param string $message
	 * @return $this
	 */
	public function error($message);

	/**
	 * Logs a critical message.
	 *
	 * @param string $message
	 * @return $this
	 */
	public function critical($message);

	/**
	 * Logs an alert message.
	 *
	 * @param string $message
	 * @return $this
	 */
	public function alert($message);

	/**
	 * Logs an emergency message.
	 *
	 * @param string $message
	 * @return $this
	 */
	public function emergency($message);

	/**
	 * Resets the instance.
	 *
	 * @return $this
	 */
	public function reset();
}