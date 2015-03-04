<?php

namespace Naga\Core\Cache;

interface iCache
{
	const StateClosed = 1;
	const StateConnected = 2;

	/**
	 * Connects to the cache.
	 *
	 * @return bool
	 */
	function connect();

	/**
	 * Disconnects from the cache.
	 */
	function disconnect();

	/**
	 * Returns whether the cache connection is alive.
	 *
	 * @return bool
	 */
	function connected();

	/**
	 * Stores an item in the cache. Ttl is in seconds.
	 *
	 * @param string $name
	 * @param mixed $value
	 * @param int $ttl
	 * @return bool
	 */
	function set($name, $value, $ttl);

	/**
	 * Stores an item in the cache forever.
	 *
	 * @param string $name
	 * @param mixed $value
	 * @return bool
	 */
	function forever($name, $value);

	/**
	 * Gets an item from the cache.
	 *
	 * @param string $name
	 * @param null|mixed $default default value if the item doesn't exist
	 * @return mixed|null
	 */
	function get($name, $default = null);

	/**
	 * Deletes an item from the cache.
	 *
	 * @param string $name
	 * @return bool
	 */
	function delete($name);

	/**
	 * Clears the entire cache.
	 *
	 * @return bool
	 */
	function clear();

	/**
	 * Sets the cache key prefix.
	 *
	 * @param string $prefix
	 */
	function setPrefix($prefix);

	/**
	 * Gets the cache key prefix.
	 *
	 * @return string
	 */
	function prefix();

	/**
	 * Increments an item's value in the cache. Returns the new value or false on failure.
	 *
	 * @param string $name
	 * @param int $amount
	 * @return bool|int
	 */
	function increment($name, $amount = 1);

	/**
	 * Decrements an item's value in the cache. Returns the new value or false on failure.
	 *
	 * @param string $name
	 * @param int $amount
	 * @return bool|int
	 */
	function decrement($name, $amount = 1);
}