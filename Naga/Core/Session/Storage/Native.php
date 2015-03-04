<?php

namespace Naga\Core\Session\Storage;

use Naga\Core\nComponent;

/**
 * Wrapper class for native php session usage.
 *
 * @author BlindingLight<bloodredshade@gmail.com>
 * @package Naga\Core\Session\Storage
 */
class Native extends nComponent implements iSessionStorage
{
	/**
	 * Calls session_start().
	 */
	public function __construct()
	{
		session_start();
	}

	/**
	 * Gets the current session id.
	 *
	 * @return string
	 */
	public function sessionId()
	{
		return session_id();
	}

	/**
	 * Sets an item in session storage.
	 *
	 * @param $name
	 * @param $value
	 */
	public function set($name, $value)
	{
		$_SESSION[$name] = $value;
	}

	/**
	 * Gets an item from session storage.
	 *
	 * @param string $name
	 * @param mixed $default default value
	 * @return mixed|null
	 */
	public function get($name, $default = null)
	{
		return isset($_SESSION[$name]) ? $_SESSION[$name] : $default;
	}

	/**
	 * Removes an item from session storage.
	 *
	 * @param string $name
	 * @return bool
	 */
	public function remove($name)
	{
		unset($_SESSION[$name]);
	}

	/**
	 * Clears the session storage.
	 */
	public function clear()
	{
		$_SESSION = array();
	}

	/**
	 * Gets the storage data as an array.
	 *
	 * @return array
	 */
	public function toArray()
	{
		return $_SESSION;
	}

	/**
	 * Ends the session.
	 *
	 * @return bool
	 */
	public function end()
	{
		return session_destroy();
	}
}