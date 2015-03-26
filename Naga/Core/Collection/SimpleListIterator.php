<?php

namespace Naga\Core\Collection;

use Naga\Core\nComponent;

/**
 * Iterator class for \Naga\Core\Collection\SimpleList
 *
 * @author BlindingLight<bloodredshade@gmail.com>
 * @package Naga\Core\Collection
 */
class SimpleListIterator extends nComponent implements \Iterator
{
	/**
	 * @var array iterable data
	 */
	private $_data;

	/**
	 * @var int list length
	 */
	private $_length;

	/**
	 * @var mixed current key
	 */
	private $_currentKey;

	/**
	 * MapIterator constructor.
	 *
	 * @param array $data iterable data
	 */
	public function __construct(&$data)
	{
		$this->_data = &$data;
		$this->_length = count($data);
		$this->_currentKey = 0;
	}

	/**
	 * Rewinds the internal array pointer.
	 */
	public function rewind()
	{
		$this->_currentKey = 0;
	}

	/**
	 * Returns the current key.
	 *
	 * @return mixed the key of the current array element
	 */
	public function key()
	{
		return $this->_currentKey;
	}

	/**
	 * Returns the current array element.
	 *
	 * @return mixed the current array element
	 */
	public function current()
	{
		return $this->_data[$this->_currentKey];
	}

	/**
	 * Moves the internal pointer to the next element.
	 */
	public function next()
	{
		++$this->_currentKey;
	}

	/**
	 * Returns whether there is an element at current position.
	 *
	 * @return bool
	 */
	public function valid()
	{
		return $this->_currentKey < $this->_length;
	}
}