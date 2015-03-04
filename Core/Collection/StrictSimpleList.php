<?php

namespace Naga\Core\Collection;

use Naga\Core\Exception\Collection\TypeMismatchException;
use Naga\Core\Exception\CollectionException;

/**
 * List implementation with strict item type.
 *
 * @package Naga\Core\Collection
 * @author  BlindingLight<bloodredshade@gmail.com>
 */
class StrictSimpleList extends SimpleList
{
	/**
	 * @var string type for type checking
	 */
	private $_type;

	/**
	 * StrictSimpleList constructor (uses the SimpleList constructor).
	 *
	 * @param string $type item type (primitive or class name)
	 * @param null|array $data list data
	 * @param bool $readOnly is read-only?
	 */
	public function __construct($type, $data = null, $readOnly = false)
	{
		$this->_type = $type;
		parent::__construct($data, $readOnly);
	}

	/**
	 * Adds an item to the StrictSimpleList. Overrides it's parent implementation (SimpleList),
	 * checks the item's type.
	 *
	 * @param mixed $index
	 * @param mixed $item
	 * @return $this
	 * @throws TypeMismatchException
	 */
	public function insertAt($index, $item)
	{
		if ((is_object($item) && $item instanceof $this->_type) || gettype($item) == $this->_type)
			return parent::insertAt($index, $item);

		throw new TypeMismatchException("Can't add an item with type '" . gettype($item) . "' to a Map<{$this->_type}>.");
	}
}