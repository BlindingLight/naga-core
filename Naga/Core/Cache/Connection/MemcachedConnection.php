<?php

namespace Naga\Core\Cache\Connection;

use Naga\Core\Cache\iCache;
use Naga\Core\nComponent;

/**
 * Memcached cache connection class.
 *
 * @author BlindingLight<bloodredshade@gmail.com>
 * @package Naga\Core\Cache\Connection
 */
class MemcachedConnection extends nComponent implements iCache
{
	/**
	 * @var string prefix for keys
	 */
	private $_prefix = '';
	/**
	 * @var \Memcache
	 */
	private $_instance;
	/**
	 * @var int
	 */
	private $_state;
	/**
	 * @var string
	 */
	private $_host;
	/**
	 * @var int
	 */
	private $_port;

	/**
	 * Construct. Sets the key prefix if specified.
	 *
	 * @param string $host
	 * @param int $port
	 * @param string $prefix
	 */
	public function __construct($host, $port = 11211, $prefix = '')
	{
		$this->setPrefix($prefix);
		$this->_instance = new \Memcache();
		$this->_state = self::StateClosed;
		$this->_host = $host;
		$this->_port = $port;
	}

	/**
	 * Connects to the cache.
	 *
	 * @return bool
	 */
	public function connect()
	{
		if ($this->_state == self::StateConnected)
			return true;

		if (@$this->_instance->pconnect($this->_host, $this->_port))
		{
			$this->_state = self::StateConnected;
			return true;
		}

		return false;
	}

	/**
	 * Disconnects from the cache.
	 */
	public function disconnect()
	{
		$this->_instance->close();
		$this->_state = self::StateClosed;
	}

	/**
	 * Returns whether the cache connection is alive.
	 *
	 * @return bool
	 */
	public function connected()
	{
		return $this->_state == self::StateConnected;
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
		if ($this->_state == self::StateClosed)
			return false;

		return $this->_instance->set($this->prefix() . $name, $value, MEMCACHE_COMPRESSED, $ttl);
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
		return $this->set($name, $value, 0);
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
		if ($this->_state == self::StateClosed)
			return $default;

		$value = $this->_instance->get($this->prefix() . $name);
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
		if ($this->_state == self::StateClosed)
			return false;

		return $this->_instance->delete($name);
	}

	/**
	 * Clears the entire cache.
	 *
	 * @return bool
	 */
	public function clear()
	{
		if ($this->_state == self::StateClosed)
			return false;

		return $this->_instance->flush();
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
		if ($this->_state == self::StateClosed)
			return false;

		return $this->_instance->increment($this->prefix() . $name, $amount);
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
		if ($this->_state == self::StateClosed)
			return false;

		return $this->_instance->decrement($this->prefix() . $name, $amount);
	}
}