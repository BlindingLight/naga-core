<?php

namespace Naga\Core\Email;

use Naga\Core\Exception\EmailException;
use Naga\Core\nComponent;

/**
 * Basic class for managing multiple email connections.
 *
 * @author BlindingLight<bloodredshade@gmail.com>
 * @package Naga\Core\Email
 */
class Email extends nComponent
{
	/**
	 * @var array connection instances
	 */
	private $_connections = array();
	/**
	 * @var string default connection name
	 */
	private $_defaultConnection = 'default';

	/**
	 * Sets the default connection name.
	 *
	 * @param string $name
	 */
	public function setDefaultConnection($name)
	{
		if ($name)
			$this->_defaultConnection = $name;
	}

	/**
	 * Adds an email connection instance.
	 *
	 * @param string $name
	 * @param iEmailConnection $connection
	 */
	public function addConnection($name, iEmailConnection $connection)
	{
		$this->_connections[$name] = $connection;
	}

	/**
	 * Gets an email connection instance.
	 *
	 * @param null|string $name
	 * @return \Naga\Core\Email\iEmailConnection
	 * @throws \Naga\Core\Exception\EmailException
	 */
	public function connection($name = null)
	{
		if (!$name)
			return $this->defaultConnection();

		if (!isset($this->_connections[$name]))
			throw new EmailException("Can't get email connection {$name}, doesn't exist.");

		return $this->_connections[$name];
	}

	/**
	 * Gets the default email connection instance.
	 *
	 * @return iEmailConnection
	 */
	public function defaultConnection()
	{
		return $this->connection($this->_defaultConnection);
	}
}