<?php

namespace Naga\Core\Cache\Connection;

use Naga\Core\Cache\iCache;
use Naga\Core\nComponent;

/**
 * APC cache connection class.
 *
 * @author BlindingLight<bloodredshade@gmail.com>
 * @package Naga\Core\Cache\Connection
 */
class ApcConnection extends nComponent implements iCache
{
	/**
	 * @var string prefix for keys
	 */
	private $_prefix = '';

	/**
	 * Construct. Sets the key prefix if specified.
	 *
	 * @param string $prefix
	 */
	public function __construct($prefix = '')
	{
		$this->setPrefix($prefix);
	}

	/**
	 * Connects to the cache.
	 *
	 * @return bool
	 */
	public function connect()
	{
		return function_exists('apc_get');
	}

	/**
	 * Disconnects from the cache.
	 */
	public function disconnect()
	{
	}

	/**
	 * Returns whether the cache connection is alive.
	 *
	 * @return bool
	 */
	public function connected()
	{
		return function_exists('apc_get');
	}

	/**
	 * Stores an item in the cache. Ttl is in seconds.
	 *
	 * @param string $name
	 * @param mixed $value
	 * @param int $ttl
	 * @return bool
	 */
	public function set($name, $value, $ttl)
	{
		return apc_store($this->prefix() . $name, $value, $ttl);
	}

	/**
	 * Stores an item in the cache forever.
	 *
	 * @param string $name
	 * @param mixed $value
	 * @return bool
	 */
	public function forever($name, $value)
	{
		return apc_store($this->prefix() . $name, $value, 0);
	}

	/**
	 * Gets an item from the cache.
	 *
	 * @param string $name
	 * @param null|mixed $default default value if the item doesn't exist
	 * @return mixed|null
	 */
	public function get($name, $default = null)
	{
		$value = apc_fetch($this->prefix() . $name);
		return $value ? $value : $default;
	}

	/**
	 * Deletes an item from the cache.
	 *
	 * @param string $name
	 * @return bool
	 */
	public function delete($name)
	{
		$success = apc_delete($this->prefix() . $name);
		return $success === false || is_array($success) ? false : true;
	}

	/**
	 * Clears the entire cache.
	 *
	 * @return bool
	 */
	public function clear()
	{
		return apc_clear_cache();
	}

	/**
	 * Sets the cache key prefix.
	 *
	 * @param string $prefix
	 */
	public function setPrefix($prefix)
	{
		$this->_prefix = $prefix;
	}

	/**
	 * Gets the cache key prefix.
	 *
	 * @return string
	 */
	public function prefix()
	{
		return $this->_prefix;
	}

	/**
	 * Increments an item's value in the cache. Returns the new value or false on failure.
	 *
	 * @param string $name
	 * @param int $amount
	 * @return bool|int
	 */
	public function increment($name, $amount = 1)
	{
		return apc_inc($this->prefix() . $name, $amount);
	}

	/**
	 * Decrements an item's value in the cache. Returns the new value or false on failure.
	 *
	 * @param string $name
	 * @param int $amount
	 * @return bool|int
	 */
	public function decrement($name, $amount = 1)
	{
		return apc_dec($this->prefix() . $name, $amount);
	}
}