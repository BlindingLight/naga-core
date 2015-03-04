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
	 * Ends the session.
	 *
	 * @return bool
	 */
	function end();
}