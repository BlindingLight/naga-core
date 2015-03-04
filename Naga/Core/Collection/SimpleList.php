<?php

namespace Naga\Core\Collection;

use Naga\Core\Exception\Collection\InvalidSourceException;
use Naga\Core\Exception\Collection\OutOfBoundsException;
use Naga\Core\Exception\Collection\ReadOnlyException;
use Naga\Core\Exception\CollectionException;
use Naga\Core\nComponent;

/**
 * A simple iterable list.
 *
 * @author BlindingLight<bloodredshade@gmail.com>
 * @package Naga\Core\Collection
 */
class SimpleList extends nComponent implements \IteratorAggregate, \Countable, \ArrayAccess
{
	/**
	 * @var bool list is read-only?
	 */
	private $_readOnly = false;

	/**
	 * @var array list data
	 */
	private $_data;

	/**
	 * @var int item count
	 */
	private $_length = 0;

	/**
	 * List constructor. Fills the list with an array or \Traversable object data.
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
	 * Adds an item into the list.
	 *
	 * @param mixed $item
	 * @return $this
	 * @throws ReadOnlyException
	 */
	public function add($item)
	{
		if ($this->_readOnly)
			throw new ReadOnlyException("Can't add item to a read-only list.");

		$this->insertAt($this->_length, $item);

		return $this;
	}

	/**
	 * Inserts an item at the specified position.
	 * Original item at the position and the next items
	 * will be moved one step towards the end.
	 *
	 * @param int $index
	 * @param mixed $item
	 * @throws ReadOnlyException
	 * @throws OutOfBoundsException
	 */
	public function insertAt($index, $item)
	{
		if ($this->_readOnly)
			throw new ReadOnlyException("Can't add item to a read-only list.");

		// if we want to insert to the end of the list
		if ($index === $this->_length)
			$this->_data[++$this->_length] = $item;
		else if($index >= 0 && $index < $this->_length)
		{
			array_splice($this->_data, $index, 0, array($item));
			++$this->_length;
		}
		else
			throw new OutOfBoundsException("List index '{$index}' is out of bound.");
	}

	/**
	 * Removes the item with the specified key.
	 *
	 * @param mixed $index
	 * @return $this
	 * @throws \Naga\Core\Exception\CollectionException
	 */
	public function remove($index)
	{
		if ($this->_readOnly)
			throw new CollectionException("Can't remove item from a read-only list.");

		$this->removeAt($index);
		return $this;
	}

	/**
	 * Removes an item at the specified position.
	 *
	 * @param integer $index the index of the item to be removed.
	 * @return $this
	 * @throws \Naga\Core\Exception\CollectionException
	 */
	public function removeAt($index)
	{
		if ($this->_readOnly)
			throw new CollectionException("Can't remove item from a read-only list.");

		if ($index >= 0 && $index < $this->_length)
		{
			$this->_length--;
			if ($index === $this->_length)
				array_pop($this->_data);
			else
				array_splice($this->_data, $index, 1);

			return $this;
		}
		else
			throw new CollectionException("removeAt($index) failed: Index is out of bound.");
	}

	/**
	 * Returns the item at the specified offset. Same as itemAt().
	 *
	 * @param int $index
	 * @return null|mixed
	 */
	public function get($index)
	{
		return $this->itemAt($index);
	}

	/**
	 * Returns the item at the specified offset.
	 *
	 * @param int $index
	 * @return null|mixed
	 */
	public function itemAt($index)
	{
		return isset($this->_data[$index]) ? $this->_data[$index] : null;
	}

	/**
	 * Clears the list.
	 *
	 * @return \Naga\Core\Collection\SimpleList
	 * @throws ReadOnlyException
	 */
	public function clear()
	{
		if ($this->_readOnly)
			throw new ReadOnlyException("Can't clear a read-only list.");

		$this->_data = array();

		return $this;
	}

	/**
	 * Returns whether the list contains the item. (the item, not index!)
	 *
	 * @param mixed $item
	 * @return bool
	 */
	public function contains($item)
	{
		return $this->indexOf($item) !== -1;
	}

	/**
	 * Gets an item's index in the list or -1 if not found.
	 *
	 * @param mixed $item
	 * @return int
	 */
	public function indexOf($item)
	{
		if (($index = array_search($item, $this->_data, true)) !== false)
			return $index;
		else
			return -1;
	}

	/**
	 * Gets the list data as array.
	 *
	 * @return mixed
	 */
	public function toArray()
	{
		return $this->_data;
	}

	/**
	 * Copies the source data into the list. Existing data will be cleared first.
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

			// must $this->add for StrictList check
			foreach($data as $item)
				$this->add($item);
		}
		else if (!is_null($data))
			throw new InvalidSourceException("Can't copy data to list, source must be a \\Traversable object or an array.");

		return $this;
	}

	/**
	 * Merges the list data with an array or \Traversable object data using array_merge.
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

		$this->_data = array_merge($this->_data, $data);

		return $this;
	}

	/**
	 * Sets that the list is read only or not.
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
	 * Returns that the list is read only or not.
	 *
	 * @return bool
	 */
	public function readOnly()
	{
		return $this->_readOnly;
	}

	/**
	 * Returns an iterator for traversing the items in the list.
	 *
	 * @return \Traversable|SimpleListIterator SimpleListIterator instance
	 */
	public function getIterator()
	{
		return new SimpleListIterator($this->_data);
	}

	/**
	 * Returns the number of items in the list.
	 *
	 * @return int number of items
	 */
	public function count()
	{
		return $this->_length;
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
	 * @return mixed the element at the offset, null if no element is found at the offset
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