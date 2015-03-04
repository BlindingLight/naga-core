<?php

namespace Naga\Core\Session;

use Naga\Core\nComponent;

/**
 * Helper class for managing sessions.
 *
 * @author BlindingLight<bloodredshade@gmail.com>
 * @package Naga\Core\Session
 */
class SessionManager extends nComponent
{
	/**
	 * Construct.
	 *
	 * @param Storage\iSessionStorage $storage
	 */
	public function __construct(Storage\iSessionStorage $storage)
	{
		$this->registerComponent('storage', $storage);
	}

	/**
	 * Clears the session storage.
	 */
	public function clear()
	{
		$this->storage()->clear();
	}

	/**
	 * Removes an item from session storage.
	 *
	 * @param string $name
	 * @return bool
	 */
	public function remove($name)
	{
		return $this->storage()->remove($name);
	}

	/**
	 * Sets an item in session storage.
	 *
	 * @param string $name
	 * @param mixed $value
	 * @return bool
	 */
	public function set($name, $value)
	{
		return $this->storage()->set($name, $value);
	}

	/**
	 * Gets an item from session storage.
	 *
	 * @param string $name
	 * @return mixed
	 */
	public function get($name)
	{
		return $this->storage()->get($name);
	}

	/**
	 * Ends the session.
	 *
	 * @return bool
	 */
	public function end()
	{
		return $this->storage()->end();
	}

	/**
	 * Gets the storage instance.
	 *
	 * @return Storage\iSessionStorage
	 */
	public function storage()
	{
		return $this->component('storage');
	}
}