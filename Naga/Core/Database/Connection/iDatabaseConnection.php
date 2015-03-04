<?php

namespace Naga\Core\Database\Connection;

/**
 * Interface for database connection classes.
 *
 * @author BlindingLight<bloodredshade@gmail.com>
 * @package Naga\Core\Database\Connection
 */
interface iDatabaseConnection
{
	/**
	 * Gets the connection name.
	 *
	 * @return string
	 */
	function name();

	/**
	 * Connects to the database.
	 *
	 * @return bool
	 */
	function connect();

	/**
	 * Disconnects from the database.
	 */
	function disconnect();

	/**
	 * Returns whether the database connection is alive.
	 *
	 * @return bool
	 */
	function connected();

	/**
	 * Begins a transaction.
	 */
	function begin();

	/**
	 * Performs commit (end of transaction).
	 */
	function commit();

	/**
	 * Performs a rollback. If $savepoint specified, rollbacks to that.
	 *
	 * @param null|string $savepoint
	 */
	function rollback($savepoint = null);

	/**
	 * Creates a savepoint.
	 *
	 * @param string $savepoint
	 */
	function savepoint($savepoint);

	/**
	 * Performs a prepared query.
	 *
	 * @param string $query
	 * @param array $args
	 * @return array|int
	 */
	function query($query, array $args);

	/**
	 * Performs a prepared query, and returns one row. If there are more than one row, or no row
	 * returned, throws a DatabaseQueryException.
	 *
	 * @param string $query
	 * @param array $args
	 * @return object
	 */
	function queryOne($query, array $args);

	/**
	 * Performs a "raw" query. Usable for queries that don't need parameters.
	 *
	 * @param string $query
	 * @return array|int
	 */
	function rawQuery($query);

	/**
	 * Performs a "raw" query. Usable for queries that don't need parameters. If there are more
	 * than one row, or no row returned, throws a DatabaseQueryException.
	 *
	 * @param string $query
	 * @return object
	 */
	function rawQueryOne($query);

	/**
	 * Gets the last error message.
	 *
	 * @return string
	 */
	function getLastError();

	/**
	 * Gets the affected rows of the last query.
	 *
	 * @return int
	 */
	function getLastAffectedRows();
}