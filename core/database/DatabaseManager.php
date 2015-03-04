<?php

namespace Naga\Core\Database;

use Naga\Core\Database\Connection\iDatabaseConnection;
use Naga\Core\Exception;
use Naga\Core\nComponent;

/**
 * Class for managing database connections. getConnectionsFromConfigArray() uses \ReflectionClass.
 *
 * @author BlindingLight<bloodredshade@gmail.com>
 * @package Naga\Core\Database
 */
class DatabaseManager extends nComponent
{
	/**
	 * Adds an iDatabaseConnection connection instance.
	 *
	 * @param iDatabaseConnection $connection
	 */
	public function addConnection(iDatabaseConnection $connection)
	{
		$this->registerComponent('connection_' . $connection->name(), $connection);
	}

	/**
	 * Adds multiple connection instances.
	 *
	 * @param array $connections
	 */
	public function addConnections(array $connections)
	{
		foreach ($connections as $connection)
			$this->addConnection($connection);
	}

	/**
	 * Gets an array containing iDatabaseConnection instances from the specified config array.
	 * Every connection in the config array must follow this format with the same order:
	 * 'type' => 'xyz', classname
	 * 'constructorArgumentName' => 'value',
	 * 'constructorArgument2Name' => 'value',
	 * ....
	 *
	 * @param array $config
	 * @return array
	 */
	public function getConnectionsFromConfigArray(array $config)
	{
		$connections = array();
		foreach ($config as $connectionName => $properties)
		{
			$arguments = array_merge(array('name' => $connectionName), (array)$properties);
			unset($arguments['type']);
			$reflector = new \ReflectionClass($properties->type);
			$instance = $reflector->newInstanceArgs($arguments);
			$connections[] = $instance;
		}

		return $connections;
	}

	/**
	 * Gets a connection instance with the specified name. If no name present, returns the
	 * 'default' connection.
	 *
	 * @param string $name
	 * @return \Naga\Core\Database\Connection\iDatabaseConnection
	 * @throws \Naga\Core\Exception\DatabaseException
	 */
	public function get($name = 'default')
	{
		try
		{
			if (!$name)
				$name = 'default';

			return $this->component('connection_' . $name);
		}
		catch (Exception\NotFoundException $e)
		{
			throw new Exception\DatabaseException("Connection {$name} not found.");
		}
	}

	/**
	 * Calls disconnect() on all connection instances.
	 */
	public function disconnectAll()
	{
		foreach ($this->getAllConnections() as $connection)
			$connection->disconnect();
	}

	/**
	 * Gets all connection instances.
	 *
	 * @return array
	 */
	public function getAllConnections()
	{
		$components = $this->registeredComponents();
		$connections = array();
		foreach ($components as $component)
		{
			if ($component->instance instanceof iDatabaseConnection)
				$connections[$component->name()] = $component->instance;
		}

		return $connections;
	}
}