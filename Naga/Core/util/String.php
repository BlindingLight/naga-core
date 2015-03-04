<?php

namespace Naga\Core\Util;

use Naga\Core\nComponent;

/**
 * Basic string related functionality. Requires mbstring php extension.
 *
 * @package Naga\Core\Util
 * @author  BlindingLight<bloodredshade@gmail.com>
 */
class String extends nComponent
{
	/**
	 * @var string current value
	 */
	protected $_value = '';

	/**
	 * @var string encoding
	 */
	protected $_encoding = '';

	/**
	 * Sets current value.
	 *
	 * @param string $value
	 * @param string $encoding
	 */
	public function __construct($value = '', $encoding = 'UTF-8')
	{
		$this->_value = $value;
		$this->_encoding = $encoding;
	}

	/**
	 * Sets and gets value. If $value is null it simply returns
	 * the current value.
	 *
	 * @param null $value
	 * @return null|string
	 */
	public function value($value = null)
	{
		if ($value)
			$this->_value = $value;

		return $this->_value;
	}

	/**
	 * Sets encoding.
	 *
	 * @param string $encoding
	 */
	public function setEncoding($encoding)
	{
		$this->_encoding = $encoding;
	}

	/**
	 * Returns $value when used as a string.
	 *
	 * @return string
	 */
	public function __toString()
	{
		return $this->_value;
	}

	/**
	 * Gets string length.
	 *
	 * @return int
	 */
	public function getLength()
	{
		return static::length($this->_value, $this->_encoding);
	}

	/**
	 * Gets a string part.
	 *
	 * @param int $offset
	 * @param int $length
	 * @return string
	 */
	public function getSubstring($offset, $length)
	{
		return static::substring($this->_value, $offset, $length, $this->_encoding);
	}

	/**
	 * Gets string length.
	 *
	 * @param string $string
	 * @param string $encoding
	 * @return int
	 */
	public static function length($string, $encoding = 'UTF-8')
	{
		return mb_strlen($string, $encoding);
	}

	/**
	 * Gets a string part.
	 *
	 * @param string $string
	 * @param int $offset
	 * @param int $length
	 * @param string $encoding
	 * @return string
	 */
	public static function substring($string, $offset, $length, $encoding = 'UTF-8')
	{
		return mb_substr($string, $offset, $length, $encoding);
	}
}