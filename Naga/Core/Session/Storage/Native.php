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
	}

	/**
	 * Gets or sets the current session id.
	 *
	 * @param string $sessionId
	 * @return string
	 */
	public function sessionId($sessionId = null)
	{
		return session_id($sessionId);
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
	 * Starts the session.
	 *
	 * @param mixed $sessionId
	 * @return bool
	 */
	public function start($sessionId = null)
	{
		if ($sessionId)
			session_id($sessionId);

		return session_start();
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

	/**
	 * Regenerates session id.
	 *
	 * @param bool $deleteOldSession Whether to delete the old associated session file or not.
	 * @return bool
	 */
	public function regenerateId($deleteOldSession = false)
	{
		return session_regenerate_id($deleteOldSession);
	}
}