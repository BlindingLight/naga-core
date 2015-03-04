<?php

namespace Naga\Core\Database\Connection\MySQL;

use Naga\Core\Database\Connection\CacheableDatabaseConnection;
use Naga\Core\Exception;

/**
 * MySQL connection class. It uses PDO.
 *
 * @author BlindingLight<bloodredshade@gmail.com>
 * @package Naga\Core\Database\Connection\MySQL
 */
class MySqlConnection extends CacheableDatabaseConnection
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
	 * @var string
	 */
	private $_user;
	/**
	 * @var string
	 */
	private $_password;
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
	 * @param string $charset
	 */
	public function __construct($name, $host, $port, $user, $password, $database, $persistent = false, $lazyConnect = true,
								$charset = 'utf8')
	{
		$this->_connectionString = 'mysql:host=' . $host . ';dbname=' . $database . ';charset=utf8';
		$this->_user = $user;
		$this->_password = $password;
		$this->_isPersistent = (bool)$persistent;
		$this->setName($name);
		$this->_lazyConnect = $lazyConnect;
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
				$this->_user,
				$this->_password,
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
		$connected = $this->_pdo instanceof \PDO;
		if ($this->_lazyConnect && !$connected)
		{
			$this->connect();
			return true;
		}

		return $connected;
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
		if (!$statement->execute($args))
			throw new Exception\DatabaseQueryException("Query error: " . $this->getLastError());

		$this->_lastAffectedRows = $statement->rowCount();
		// insert, update, delete -> return affected rows
		if ($this->_lastAffectedRows && strpos(strtolower(trim($query)), 'select') !== 0)
			return true;

		// create statements
		if (stripos($query, 'create') === 0)
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
			$statement->execute();
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
			$this->_pdo->query('ROLLBACK TO SAVEPOINT ' . $savepoint);
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
	 * From MySQL docs:
	 * The RELEASE SAVEPOINT statement removes the named savepoint from the set of savepoints
	 * of the current transaction. No commit or rollback occurs. It is an error if the savepoint
	 * does not exist.
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