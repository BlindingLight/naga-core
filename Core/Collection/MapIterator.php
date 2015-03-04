<?php

namespace Naga\Core\Collection;

use Naga\Core\nComponent;

/**
 * Iterator class for \Naga\Core\Collection\Map
 *
 * @author BlindingLight<bloodredshade@gmail.com>
 * @package Naga\Core\Collection
 */
class MapIterator extends nComponent implements \Iterator
{
	/**
	 * @var array iterable data
	 */
	private $_data;

	/**
	 * @var array keys in the map
	 */
	private $_keys;

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
		$this->_keys = array_keys($data);
		$this->_currentKey = reset($this->_keys);
	}

	/**
	 * Rewinds the internal array pointer.
	 */
	public function rewind()
	{
		$this->_currentKey = reset($this->_keys);
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
		$this->_currentKey = next($this->_keys);
	}

	/**
	 * Returns whether there is an element at current position.
	 *
	 * @return boolean
	 */
	public function valid()
	{
		return $this->_currentKey !== false;
	}
}