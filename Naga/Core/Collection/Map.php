<?php

namespace Naga\Core\Collection;

use Naga\Core\Exception\Collection\InvalidSourceException;
use Naga\Core\Exception\Collection\ReadOnlyException;
use Naga\Core\nComponent;

/**
 * A basic map.
 *
 * @author BlindingLight<bloodredshade@gmail.com>
 * @package Naga\Core\Collection
 */
class Map extends nComponent implements \IteratorAggregate, \Countable, \ArrayAccess
{
	/**
	 * @var bool map is read-only?
	 */
	private $_readOnly = false;

	/**
	 * @var array map data
	 */
	private $_data = array();

	/**
	 * Map constructor. Fills the map with an array or \Traversable object data.
	 *
	 * @param null|\Traversable|array $data
	 * @param bool $readOnly
	 */
	public function __construct($data = null, $readOnly = false)
	{
		if ($data instanceof \Traversable || is_array($data))
			$this->copyFrom($data);

		$this->setReadOnly($readOnly);
	}

	/**
	 * Gets the data as a json string.
	 *
	 * @return string
	 */
	public function __toString()
	{
		return json_encode($this->toArray());
	}

	/**
	 * Adds an item into the map. If the item already exists, it'll be overwritten.
	 *
	 * @param mixed $key
	 * @param mixed $value
	 * @return $this
	 * @throws ReadOnlyException
	 */
	public function add($key, $value)
	{
		if ($this->_readOnly)
			throw new ReadOnlyException("Can't add item to a read-only map.");

		$this->_data[$key] = $value;

		return $this;
	}

	/**
	 * Removes the item with the specified key.
	 *
	 * @param mixed $key
	 * @return $this
	 * @throws ReadOnlyException
	 */
	public function remove($key)
	{
		if ($this->_readOnly)
			throw new ReadOnlyException("Can't remove item from a read-only map.");

		if (isset($this->_data[$key]))
			unset($this->_data[$key]);

		return $this;
	}

	/**
	 * Returns the item with the specified key, or null if the item doesn't exist.
	 * You can use $key like object.property.subproperty or array.index.subindex.
	 * You can use numeric indexes if property is an array.
	 *
	 * Examples:
	 * 	array.0
	 * 	array.testIndex.subIndex
	 * 	object.propertyName.array.0.subIndex
	 *
	 * @param mixed $key
	 * @return null|mixed
	 */
	public function get($key)
	{
		// we check whether a property with name $key exists, even if it contains .
		$value = isset($this->_data[$key]) ? $this->_data[$key] : null;

		if ($value !== null)
			return $value;

		// if we got . in key we check for subproperties
		if (strpos($key, '.') !== false)
		{
			$parts = explode('.', $key);
			// part 0 doesn't exist so we return null
			if (!isset($this->_data[$parts[0]]))
				return null;

			$value = $this->_data[$parts[0]];
			// checking parts
			foreach ($parts as $idx => $part)
			{
				// skipping first part
				if (!$idx)
					continue;

				// if object we check whether property with name $part exists
				if (is_object($value))
				{
					if (!isset($value->{$part}))
						return null;

					$value = $value->{$part};
				}
				// if array we chekc whether index with name $part exists
				else if (is_array($value))
				{
					if (!isset($value[$part]))
						return null;

					$value = $value[$part];
				}
			}
		}

		return $value;
	}

	/**
	 * Clears the map.
	 *
	 * @return $this
	 * @throws ReadOnlyException
	 */
	public function clear()
	{
		if ($this->_readOnly)
			throw new ReadOnlyException("Can't clear a read-only map.");

		$this->_data = array();

		return $this;
	}

	/**
	 * Returns whether the map contains an item with the specified key.
	 *
	 * @param mixed $key
	 * @return bool
	 */
	public function contains($key)
	{
		return isset($this->_data[$key]);
	}

	/**
	 * Gets the map data as array.
	 *
	 * @return array
	 */
	public function toArray()
	{
		return $this->_data;
	}

	/**
	 * Copies the source data into the map. Existing data will be cleared first.
	 *
	 * @param array|\Traversable $data
	 * @return $this
	 * @throws InvalidSourceException
	 */
	public function copyFrom($data)
	{
		if ($data instanceof \Traversable || is_array($data))
		{
			if ($this->count() > 0)
				$this->clear();
			if (method_exists($data, 'toArray'))
				$data = $data->toArray();

			// must $this->add for StrictMap check
			foreach($data as $key => $value)
				$this->add($key, $value);
		}
		else if (!is_null($data))
			throw new InvalidSourceException("Can't copy data to map, source must be a \\Traversable object or an array.");

		return $this;
	}

	/**
	 * Merges the map data with an array or \Traversable object data using array_merge.
	 *
	 * @param array|\Traversable $data
	 * @return $this
	 * @throws InvalidSourceException
	 */
	public function mergeWith($data)
	{
		if (!is_array($data) && !($data instanceof \Traversable))
			throw new InvalidSourceException("Can't merge with data, source must be a \\Traversable object or an array.");
		$tmp = is_array($data) ? $data : array();
		if (!is_array($data))
		{
			foreach ($data as $key => $value)
				$tmp[$key] = $value;
		}

		$this->_data = array_merge($this->_data, $tmp);

		return $this;
	}

	/**
	 * Sets that the map is read only or not.
	 *
	 * @param bool $readOnly
	 * @return $this
	 */
	public function setReadOnly($readOnly)
	{
		$this->_readOnly = !(!$readOnly);
		return $this;
	}

	/**
	 * Returns that the map is read only or not.
	 *
	 * @return bool
	 */
	public function readOnly()
	{
		return $this->_readOnly;
	}

	/**
	 * Returns an iterator for traversing the items in the map.
	 *
	 * @return \Traversable|MapIterator MapIterator instance
	 */
	public function getIterator()
	{
		return new MapIterator($this->_data);
	}

	/**
	 * Returns the number of items in the map.
	 *
	 * @return int number of items
	 */
	public function count()
	{
		return count($this->_data);
	}

	/**
	 * Returns whether there is an element at the specified offset.
	 *
	 * @param mixed $offset the offset to check on
	 * @return bool
	 */
	public function offsetExists($offset)
	{
		return $this->contains($offset);
	}

	/**
	 * Returns the element at the specified offset.
	 *
	 * @param int $offset the offset to retrieve element.
	 * @return mixed|null the element at the offset, null if no element is found at the offset
	 */
	public function offsetGet($offset)
	{
		return $this->get($offset);
	}

	/**
	 * Sets the element at the specified offset.
	 *
	 * @param int $offset the offset to set element
	 * @param mixed $item the element value
	 */
	public function offsetSet($offset, $item)
	{
		$this->add($offset, $item);
	}

	/**
	 * Unsets the element at the specified offset.
	 *
	 * @param mixed $offset the offset to unset element
	 */
	public function offsetUnset($offset)
	{
		$this->remove($offset);
	}
}