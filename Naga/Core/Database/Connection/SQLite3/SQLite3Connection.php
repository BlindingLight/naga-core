<?php

namespace Naga\Core\Database\Connection\SQLite3;

use Naga\Core\Database\Connection\CacheableDatabaseConnection;
use Naga\Core\Exception;

/**
 * SQLite3 connection class. It uses PDO.
 *
 * @author BlindingLight<bloodredshade@gmail.com>
 * @package Naga\Core\Database\Connection\SQLite3
 */
class SQLite3Connection extends CacheableDatabaseConnection
{
	/**
	 * @var \PDO
	 */
	private $_pdo;
	/**
	 * @var string
	 */
	private $_connectionString;
	/**
	 * @var bool
	 */
	private $_isPersistent;

	/**
	 * Construct.
	 *
	 * @param string $name connection name
	 * @param string $dsn SQLite file path or :memory: for in-memory database
	 * @param bool $persistent persistent connection?
	 */
	public function __construct($name, $dsn = ':memory:', $persistent = false)
	{
		// creating database file if not exists
		if ($dsn != ':memory:' && !file_exists($dsn))
			@touch($dsn);

		$this->_connectionString = 'sqlite:' . $dsn;
		$this->_isPersistent = (bool)$persistent;
		$this->setName($name);
	}

	/**
	 * Connects to the database.
	 *
	 * @return bool
	 */
	public function connect()
	{
		try
		{
			$this->_pdo = new \PDO(
				$this->_connectionString,
				null,
				null,
				array(
					\PDO::ATTR_PERSISTENT => $this->_isPersistent
				)
			);

			$this->_pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
			return true;
		}
		catch (\Exception $e)
		{
			$this->_lastErrorMessage = $e->getMessage();
			return false;
		}
	}

	/**
	 * Disconnects from the database.
	 */
	public function disconnect()
	{
		$this->_pdo = null;
	}

	/**
	 * Returns whether the database connection is alive.
	 *
	 * @return bool
	 */
	public function connected()
	{
		return $this->_pdo instanceof \PDO;
	}

	/**
	 * Performs a prepared query.
	 *
	 * @param string $query
	 * @param array $args
	 * @return array|int
	 * @throws \Naga\Core\Exception\DatabaseException
	 * @throws \Naga\Core\Exception\DatabaseQueryException
	 */
	public function query($query, array $args)
	{
		if (!$this->connected())
			throw new Exception\DatabaseException("Can't do query on a closed connection.");

		$statement = $this->_pdo->prepare($query);
		foreach ($args as $idx => $arg)
		{
			$key = is_numeric($idx) ? $idx + 1 : $idx;
			$statement->bindParam($key, $arg);
		}

		if (!$statement->execute())
			throw new Exception\DatabaseQueryException("Query error: " . $this->getLastError());

		$this->_lastAffectedRows = $statement->rowCount();
		// insert, update, delete -> return affected rows
		if ($this->_lastAffectedRows)
			return true;

		return $statement->fetchAll(\PDO::FETCH_OBJ);
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

		try
		{
			$statement = $this->_pdo->query($query);
			$statement->closeCursor();
		}
		catch (\PDOException $e)
		{
			throw new Exception\DatabaseQueryException("Query error: " . $e->getMessage());
		}

		if ($statement === false)
			throw new Exception\DatabaseQueryException("Query error: " . $this->getLastError());

		return $statement->fetchAll(\PDO::FETCH_OBJ);
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
	 * Gets the last error message. If \PDO::errorCode() not equals to 0, returns the error code.
	 *
	 * @return string
	 * @throws \Naga\Core\Exception\DatabaseException
	 */
	public function getLastError()
	{
		if ($this->_pdo instanceof \PDO && $this->_pdo->errorCode())
			return $this->_pdo->errorCode();

		return parent::getLastError();
	}

	/**
	 * Gets the last inserted id.
	 *
	 * @param null|string $name Name of the sequence object from which the ID should be returned.
	 * @return string
	 */
	public function getLastInsertId($name = null)
	{
		return $this->_pdo->lastInsertId($name);
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

		$this->_pdo->beginTransaction();
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

		$this->_pdo->commit();
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
			$this->_pdo->rollBack();
		else
			$this->_pdo->query('ROLLBACK TRANSACTION TO SAVEPOINT ' . $savepoint);
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

		$this->_pdo->query('SAVEPOINT ' . $savepoint);
	}

	/**
	 * From SQLite docs:
	 * The RELEASE command causes all savepoints back to and including the most recent savepoint with
	 * a matching name to be removed from the transaction stack. The RELEASE of an inner transaction does
	 * not cause any changes to be written to the database file; it merely removes savepoints from the
	 * transaction stack such that it is no longer possible to ROLLBACK TO those savepoints.
	 *
	 * @param string $savepoint
	 * @throws \Naga\Core\Exception\DatabaseException
	 */
	public function releaseSavepoint($savepoint)
	{
		if (!$this->connected())
			throw new Exception\DatabaseException("Can't release a savepoint on a closed connection.");

		$this->_pdo->query('RELEASE SAVEPOINT ' . $savepoint);
	}
}