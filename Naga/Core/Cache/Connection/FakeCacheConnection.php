<?php

namespace Naga\Core\Cache\Connection;

use Naga\Core\Cache\iCache;
use Naga\Core\nComponent;

class FakeCacheConnection extends nComponent implements iCache
{
	/**
	 * @var string prefix for keys
	 */
	private $_prefix = '';

	/**
	 * @var array stored data
	 */
	private $_data = array();

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
		return true;
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
		return true;
	}

	/**
	 * Stores an item in the cache. Ttl is unused here.
	 *
	 * @param string $name
	 * @param mixed $value
	 * @param int $ttl
	 * @return bool
	 */
	public function set($name, $value, $ttl)
	{
		return $this->_data[$this->prefix() . $name] = $value;
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
		return $this->_data[$this->prefix() . $name] = $value;
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
		$name = $this->prefix() . $name;
		return isset($this->_data[$name]) ? $this->_data[$name] : $default;
	}

	/**
	 * Deletes an item from the cache.
	 *
	 * @param string $name
	 * @return bool
	 */
	public function delete($name)
	{
		$name = $this->prefix() . $name;
		if (isset($this->_data[$name]))
			unset($this->_data[$name]);

		return true;
	}

	/**
	 * Clears the entire cache.
	 *
	 * @return bool
	 */
	public function clear()
	{
		return $this->_data = array();
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
		$name = $this->prefix() . $name;
		if (isset($this->_data[$name]))
			$this->_data[$name] += $amount;

		return $this->_data[$name];
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
		$name = $this->prefix() . $name;
		if (isset($this->_data[$name]))
			$this->_data[$name] -= $amount;

		return $this->_data[$name];
	}
}