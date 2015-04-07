<?php

namespace Naga\Core\Session\Storage;

interface iSessionStorage
{
	/**
	 * Gets the current session id.
	 *
	 * @return string
	 */
	function sessionId();

	/**
	 * Sets an item in session storage.
	 *
	 * @param $name
	 * @param $value
	 */
	function set($name, $value);

	/**
	 * Gets an item from session storage.
	 *
	 * @param string $name
	 * @param mixed $default default value
	 * @return mixed
	 */
	function get($name, $default = null);

	/**
	 * Removes an item from session storage.
	 *
	 * @param string $name
	 * @return bool
	 */
	function remove($name);

	/**
	 * Clears the session storage.
	 */
	function clear();

	/**
	 * Gets the storage data as an array.
	 *
	 * @return array
	 */
	function toArray();

	/**
	 * Starts the session.
	 *
	 * @param mixed $sessionId
	 * @return bool
	 */
	function start($sessionId = null);

	/**
	 * Ends the session.
	 *
	 * @return bool
	 */
	function end();

	/**
	 * Regenerates session id.
	 *
	 * @param bool $deleteOldSession Whether to delete the old associated session data or not.
	 * @return bool
	 */
	function regenerateId($deleteOldSession = false);
}