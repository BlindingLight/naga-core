<?php

namespace Naga\Core\Response;

use Naga\Core\nComponent;

/**
 * A class for html responses. It simply echoes it's $_data.
 *
 * @author BlindingLight<bloodredshade@gmail.com>
 * @package Naga\Core\Response
 */
class HtmlResponse extends Response
{
	/**
	 * @var string html output
	 */
	private $_data;

	/**
	 * HtmlResponse construct.
	 *
	 * @param string $content
	 */
	public function __construct($content = '')
	{
		$this->_data = $content;
		$this->setMimeType('text/html');
		$this->setHeader('Content-type', $this->mimeType());
	}

	/**
	 * Sets the response content. Existing content will be cleared.
	 *
	 * @param string $content
	 * @return \Naga\Core\Response\HtmlResponse
	 */
	public function setContent($content)
	{
		$this->_data = (string)$content;
		return $this;
	}

	/**
	 * Appends a string to the end of the html output.
	 *
	 * @param string $content
	 * @return \Naga\Core\Response\HtmlResponse
	 */
	public function append($content)
	{
		$this->_data .= (string)$content;
		return $this;
	}

	/**
	 * Prepends a string to the end of the html output.
	 *
	 * @param string $content
	 * @return \Naga\Core\Response\HtmlResponse
	 */
	public function prepend($content)
	{
		$this->_data = (string)$content . $this->_data;
		return $this;
	}

	/**
	 * Clears the html output. Overrides parent implementation.
	 *
	 * @return \Naga\Core\Response\HtmlResponse
	 */
	public function clear()
	{
		$this->_data = '';
		return $this;
	}

	/**
	 * Returns the html output.
	 *
	 * @return mixed|string
	 */
	public function data()
	{
		return $this->_data;
	}

	/**
	 * Echoes the html output, and if $exitAfter is true, calls exit().
	 *
	 * @param bool $exitAfter
	 */
	public function send($exitAfter = false)
	{
		$this->sendHeaders();
		echo $this->_data;
		if ($exitAfter)
			exit();
	}
}