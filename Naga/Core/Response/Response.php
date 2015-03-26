<?php

namespace Naga\Core\Response;

use Naga\Core\Collection\Map;
use Naga\Core\Exception\ArgumentMismatchException;

/**
 * Base class for response classes. It extends Map, so we can navigate through it's elements
 * easily.
 *
 * @author BlindingLight<bloodredshade@gmail.com>
 * @package Naga\Core\Response
 */
abstract class Response extends Map implements iResponse
{
	/**
	 * @var int response http status code
	 */
	private $_statusCode = HttpStatusCodes::StatusOk;

	/**
	 * @var string response mime type
	 */
	private $_mimeType = 'text/plain';

	/**
	 * @var array response header list
	 */
	protected $_headers = array();

	/**
	 * Sets the http status code of the response.
	 *
	 * @param int $statusCode
	 * @throws \Naga\Core\Exception\ArgumentMismatchException
	 */
	public function setStatusCode($statusCode)
	{
		if (!is_numeric($statusCode))
			throw new ArgumentMismatchException("Can't set invalid status code (must be numeric).");

		$this->_statusCode = $statusCode;
		http_response_code($this->_statusCode);
	}

	/**
	 * Get the http status code of response.
	 *
	 * @return int
	 */
	public function statusCode()
	{
		return $this->_statusCode;
	}

	/**
	 * Sets response mime type.
	 *
	 * @param string $mimeType
	 */
	public function setMimeType($mimeType)
	{
		$this->_mimeType = $mimeType;
	}

	/**
	 * Gets response mime type.
	 *
	 * @return string
	 */
	public function mimeType()
	{
		return $this->_mimeType;
	}

	/**
	 * Sets a header for the response. This method just stores the header,
	 * does not send it.
	 *
	 * @param string $name
	 * @param string $value
	 * @return $this
	 */
	public function setHeader($name, $value)
	{
		$this->_headers[$name] = $value;

		return $this;
	}

	/**
	 * Removes a header from the response.
	 *
	 * @param string $name
	 * @return $this
	 */
	public function removeHeader($name)
	{
		if (isset($this->_headers[$name]))
			unset($this->_headers[$name]);

		return $this;
	}

	/**
	 * Gets all response headers.
	 *
	 * @return array
	 */
	public function headers()
	{
		return $this->_headers;
	}

	/**
	 * Sends the response headers.
	 */
	public function sendHeaders()
	{
		foreach ($this->_headers as $name => $value)
			header("{$name}: {$value}");

		// adding naga headers
		header('X-Naga-Framework: ' . self::$_nagaFrameworkVersion . ' - ' . self::$_nagaFrameworkCodeName);
	}

	/**
	 * Sends the output, and if $exitAfter is true, calls exit().
	 *
	 * @param bool $exitAfter
	 */
	abstract function send($exitAfter = false);
}