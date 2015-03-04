<?php

namespace Naga\Core\Database\Connection\PostgreSQL;

use Naga\Core\Database\Connection\CacheableDatabaseConnection;
use Naga\Core\Exception;

/**
 * PostgreSQL connection class. It uses native functions.
 *
 * @author BlindingLight<bloodredshade@gmail.com>
 * @package Naga\Core\Database\Connection\SQLite3
 */
class PgSqlConnection extends CacheableDatabaseConnection
{
	/**
	 * @var resource connection
	 */
	private $_connection;
	/**
	 * @var string
	 */
	private $_connectionString;
	/**
	 * @var bool
	 */
	private $_isPersistent;

	/**
	 * @var bool
	 */
	private $_lazyConnect = true;

	/**
	 * Construct.
	 *
	 * @param string $name connection name
	 * @param string $host
	 * @param int $port
	 * @param string $user
	 * @param string $password
	 * @param string $database
	 * @param bool $persistent persistent connection?
	 * @param bool $lazyConnect connect automatically if the connection isn't alive when trying to query the database?
	 */
	public function __construct($name, $host, $port, $user, $password, $database, $persistent = false, $lazyConnect = true)
	{
		$this->_connectionString = 'host=' . $host . ' port=' . $port . ' dbname=' . $database . ' user=' . $user .
									' password=' . $password .' sslmode=disable';

		$this->_isPersistent = (bool)$persistent;
		$this->_lazyConnect = $lazyConnect;
		$this->setName($name);
	}

	/**
	 * Connects to the database.
	 *
	 * @return bool
	 */
	public function connect()
	{
		if (!$this->_isPersistent)
			$this->_connection = pg_connect($this->_connectionString);
		else
			$this->_connection = pg_pconnect($this->_connectionString);

		if (!$this->_connection)
		{
			$this->_lastErrorMessage = 'PgSqlConnection[' . $this->name() . ']: Failed to connect to database server.';
			return false;
		}

		return true;
	}

	/**
	 * Disconnects from the database.
	 */
	public function disconnect()
	{
		@pg_close($this->_connection);
	}

	/**
	 * Returns whether the database connection is alive.
	 *
	 * @return bool
	 */
	public function connected()
	{
		$status = $this->_connection ? pg_connection_status($this->_connection) == PGSQL_CONNECTION_OK : false;
		if ($this->_lazyConnect && !$status)
			return $this->connect();

		return $status;
	}

	/**
	 * Performs a prepared query.
	 *
	 * @param string $query
	 * @param array $args
	 * @return array|int
	 * @throws \Naga\Core\Exception\DatabaseException
	 */
	public function query($query, array $args)
	{
		if (!$this->connected())
			throw new Exception\DatabaseException("Can't do query on a closed connection.");

		$q = @pg_query_params($this->_connection, $query, $args);
		if ($q === false)
			throw new Exception\DatabaseException('Failed to execute query: ' . pg_last_error($this->_connection));

		return $this->parseResult($q);
	}

	/**
	 * Performs a prepared query, and returns one row. If there are more than one row, or no row
	 * returned, throws a DatabaseQueryException.
	 *
	 * @param string $query
	 * @param array $args
	 * @return object
	 * @throws \Naga\Core\Exception\DatabaseException
	 * @throws \Naga\Core\Exception\DatabaseQueryException
	 */
	public function queryOne($query, array $args)
	{
		if (!$this->connected())
			throw new Exception\DatabaseException("Can't do queryOne on a closed connection.");

		$result = $this->query($query, $args);
		if (!is_array($result))
			throw new Exception\DatabaseQueryException('Attempted to fetch rows from something that does not return a resultset.');

		if (count($result) == 0)
			throw new Exception\DatabaseQueryException('No row returned.');

		if (count($result) > 1)
			throw new Exception\DatabaseQueryException('Too many rows returned.');

		return $result[0];
	}

	/**
	 * Performs a "raw" query. Usable for queries that don't need parameters.
	 *
	 * @param string $query
	 * @return array|int
	 * @throws \Naga\Core\Exception\DatabaseException
	 * @throws \Naga\Core\Exception\DatabaseQueryException
	 */
	public function rawQuery($query)
	{
		if (!$this->connected())
			throw new Exception\DatabaseException("Can't do rawQuery on a closed connection.");

		$q = @pg_query($this->_connection, $query);
		if ($q === false)
		{
			$this->_lastErrorMessage = pg_last_error($this->_connection);;
			throw new Exception\DatabaseQueryException('Failed to execute query: ' . $this->_lastErrorMessage);
		}

		$result = $this->parseResult($q);
		return $result;
	}

	/**
	 * Performs a "raw" query. Usable for queries that don't need parameters. If there are more
	 * than one row, or no row returned, throws a DatabaseQueryException.
	 *
	 * @param string $query
	 * @return array|int
	 * @throws \Naga\Core\Exception\DatabaseException
	 * @throws \Naga\Core\Exception\DatabaseQueryException
	 */
	public function rawQueryOne($query)
	{
		if (!$this->connected())
			throw new Exception\DatabaseException("Can't do rawQueryOne on a closed connection.");

		$result = $this->rawQuery($query);
		if (!is_array($result))
			throw new Exception\DatabaseQueryException('Attempted to fetch rows from something that does not return a resultset.');

		if (count($result) == 0)
			throw new Exception\DatabaseQueryException('No row returned.');

		if (count($result) > 1)
			throw new Exception\DatabaseQueryException('Too many rows returned.');

		return $result[0];
	}

	/**
	 * Parses the resultset to an array filled with objects (rows) or returns the last affected rows.
	 *
	 * @param resource $result
	 * @return array|int
	 */
	protected function parseResult($result)
	{
		$this->_lastAffectedRows = @pg_affected_rows($result);

		$rows = array();
		while ($row = @pg_fetch_object($result))
		{
			foreach ($row as $key => $value)
			{
				if ($value == 't')
					$row->{$key} = true;
				if ($value == 'f')
					$row->{$key} = false;
			}
			$rows[] = $row;
		}

		// if insert, delete, update return the affected row count (and we don't need results)
		if ($row === false && !count($rows) && $this->_lastAffectedRows)
			return $this->_lastAffectedRows;

		@pg_free_result($result);

		return $rows;
	}

	/**
	 * Gets the last error message. If \PDO::errorCode() not equals to 0, returns the error code.
	 *
	 * @return string
	 * @throws \Naga\Core\Exception\DatabaseException
	 */
	public function getLastError()
	{
		return $this->_lastErrorMessage;
	}

	/**
	 * Begins a transaction.
	 *
	 * @throws \Naga\Core\Exception\DatabaseException
	 */
	public function begin()
	{
		if (!$this->connected())
			throw new Exception\DatabaseException("Can't start transaction on a closed connection.");

		$this->rawQuery('begin');
	}

	/**
	 * Performs commit (end of transaction).
	 *
	 * @throws \Naga\Core\Exception\DatabaseException
	 */
	public function commit()
	{
		if (!$this->connected())
			throw new Exception\DatabaseException("Can't do commit on a closed connection.");

		$this->rawQuery('commit');
	}

	/**
	 * Performs a rollback. If $savepoint specified, rollbacks to that.
	 *
	 * @param null|string $savepoint
	 * @throws \Naga\Core\Exception\DatabaseException
	 */
	public function rollback($savepoint = null)
	{
		if (!$this->connected())
			throw new Exception\DatabaseException("Can't do rollback transaction on a closed connection.");

		if (!$savepoint)
			$this->rawQuery('rollback');
		else
			$this->rawQuery('rollback to ' . $savepoint);
	}

	/**
	 * Creates a savepoint.
	 *
	 * @param string $savepoint
	 * @throws \Naga\Core\Exception\DatabaseException
	 */
	public function savepoint($savepoint)
	{
		if (!$this->connected())
			throw new Exception\DatabaseException("Can't create a savepoint on a closed connection.");

		$this->rawQuery('savepoint ' . $savepoint);
	}


}