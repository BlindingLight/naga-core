<?php

namespace Naga\Core\Database\Connection;

use Naga\Core\Cache\iCache;
use Naga\Core\nComponent;
use Naga\Core\Exception;

/**
 * Base class for cacheable database connections.
 *
 * @author BlindingLight<bloodredshade@gmail.com>
 * @package Naga\Core\Database\Connection
 */
abstract class CacheableDatabaseConnection extends nComponent implements  iDatabaseConnection
{
	/**
	 * @var string
	 */
	protected $_lastErrorMessage = '';
	/**
	 * @var int
	 */
	protected $_lastAffectedRows = 0;
	/**
	 * @var string
	 */
	private $_name = '';

	/**
	 * Performs a prepared query and stores the results in the cache. If there is a stored result for
	 * a query, returns that. If you want to overwrite an existing cached result, set
	 * the $forcedQuery parameter to true.
	 *
	 * @param string $query
	 * @param array $args
	 * @param int $ttl time to live in seconds, default 3600 (1 hour)
	 * @param bool $forcedQuery query the database even if there is a stored result?
	 * @return array|int
	 */
	public function cachedQuery($query, $args, $ttl = 3600, $forcedQuery = false)
	{
		$queryName = $this->getQueryCacheName($query, $args);
		$stored = $this->cache()->get($queryName, false);

		if ($stored && !$forcedQuery)
			return $stored;

		$result = $this->query($query, $args);
		$this->cache()->set(
			$queryName,
			$result,
			$ttl
		);

		return $result;
	}

	/**
	 * Performs a prepared query and stores the result in the cache. It throws DatabaseQueryException
	 * if more than one, or no row returned. If there is a stored result for
	 * a query, returns that. If you want to overwrite an existing cached result, set
	 * the $forcedQuery parameter to true.
	 *
	 * @param string $query
	 * @param array $args
	 * @param int $ttl time to live in seconds, default 3600 (1 hour)
	 * @param bool $forcedQuery query the database even if there is a stored result?
	 * @return object
	 * @throws \Naga\Core\Exception\DatabaseQueryException
	 */
	public function cachedQueryOne($query, $args, $ttl = 3600, $forcedQuery = false)
	{
		$queryName = $this->getQueryCacheName($query, $args);
		$stored = $this->cache()->get($queryName, false);

		if ($stored && !$forcedQuery)
			return $stored;

		$result = $this->query($query, $args);

		if (count($result) !== 1)
			throw new Exception\DatabaseQueryException("Returned row count for cachedQueryOne() is not equal to 1.");

		$this->cache()->set(
			$queryName,
			$result[0],
			$ttl
		);

		return $result[0];
	}

	/**
	 * Performs a raw query and stores the results in the cache. If there is a stored result for
	 * a query, returns that. If you want to overwrite an existing cached result, set
	 * the $forcedQuery parameter to true.
	 *
	 * @param string $query
	 * @param int $ttl time to live in seconds, default 3600 (1 hour)
	 * @param bool $forcedQuery query the database even if there is a stored result?
	 * @return array|int
	 */
	public function cachedRawQuery($query, $ttl = 3600, $forcedQuery = false)
	{
		$queryName = $this->getRawQueryCacheName($query);
		$stored = $this->cache()->get($queryName, false);

		if ($stored && !$forcedQuery)
			return $stored;

		$result = $this->rawQuery($query);
		$this->cache()->set(
			$queryName,
			$result,
			$ttl
		);

		return $result;
	}

	/**
	 * Performs a raw query and stores the result in the cache. It throws DatabaseQueryException
	 * if more than one, or no row returned. If there is a stored result for
	 * a query, returns that. If you want to overwrite an existing cached result, set
	 * the $forcedQuery parameter to true.
	 *
	 * @param string $query
	 * @param int $ttl time to live in seconds, default 3600 (1 hour)
	 * @param bool $forcedQuery query the database even if there is a stored result?
	 * @return mixed
	 * @throws \Naga\Core\Exception\DatabaseQueryException
	 */
	public function cachedRawQueryOne($query, $ttl = 3600, $forcedQuery = false)
	{
		$queryName = $this->getRawQueryCacheName($query);
		$stored = $this->cache()->get($queryName, false);

		if ($stored && !$forcedQuery)
			return $stored;

		$result = $this->rawQuery($query);

		if (count($result) !== 1)
			throw new Exception\DatabaseQueryException("Returned row count for cachedRawQueryOne() is not equal to 1.");

		$this->cache()->set(
			$queryName,
			$result[0],
			$ttl
		);

		return $result[0];
	}

	/**
	 * Returns the generated cache key for the query.
	 *
	 * @param string $query
	 * @param array $args
	 * @return string
	 */
	public function getQueryCacheName($query, $args)
	{
		return md5($query . var_export($args, true));
	}

	/**
	 * Returns the generated cache key for the raw query.
	 *
	 * @param string $query
	 * @return string
	 */
	public function getRawQueryCacheName($query)
	{
		return md5($query);
	}

	/**
	 * Sets the cache instance. If $cacheKeyPrefix is not null or empty string,
	 * queries will be stored with key $prefix . $generatedKey
	 *
	 * @param \Naga\Core\Cache\iCache $cache
	 * @param string $cacheKeyPrefix
	 */
	public function setCache(iCache $cache, $cacheKeyPrefix = 'cached_db_query_')
	{
		$this->registerComponent('cache', $cache);
		$this->cache()->setPrefix($cacheKeyPrefix);
	}

	/**
	 * Gets the cache instance.
	 *
	 * @return \Naga\Core\Cache\iCache
	 */
	protected function cache()
	{
		return $this->component('cache');
	}

	/**
	 * Gets the last error message.
	 *
	 * @return string
	 */
	public function getLastError()
	{
		return $this->_lastErrorMessage;
	}

	/**
	 * Gets the affected rows of the last query.
	 *
	 * @return int
	 */
	public function getLastAffectedRows()
	{
		return $this->_lastAffectedRows;
	}

	/**
	 * Sets the connection name.
	 *
	 * @param string $name
	 * @throws \Naga\Core\Exception\DatabaseException
	 */
	protected function setName($name)
	{
		if ($this->_name)
			throw new Exception\DatabaseException("Can't change name of a connection that already has a name.");

		$this->_name = $name;
	}

	/**
	 * Gets the connection name.
	 *
	 * @return string
	 */
	public function name()
	{
		return $this->_name;
	}
}