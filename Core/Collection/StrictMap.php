<?php

namespace Naga\Core\Collection;

use Naga\Core\Exception\Collection\TypeMismatchException;

/**
 * Map implementation with strict item type.
 *
 * @package Naga\Core\Collection
 * @author  BlindingLight<bloodredshade@gmail.com>
 */
class StrictMap extends Map
{
	/**
	 * @var string type for type checking
	 */
	private $_type;

	/**
	 * StrictMap constructor (uses the Map constructor).
	 *
	 * @param string $type item type (primitive or class name)
	 * @param null|array $data map data
	 * @param bool $readOnly is read-only?
	 */
	public function __construct($type, $data = null, $readOnly = false)
	{
		$this->_type = $type;
		parent::__construct($data, $readOnly);
	}

	/**
	 * Adds an item to the StrictMap. Overrides it's parent implementation (Map),
	 * checks the item's type.
	 *
	 * @param mixed $key
	 * @param mixed $value
	 * @return $this
	 * @throws TypeMismatchException
	 */
	public function add($key, $value)
	{
		if ((is_object($value) && $value instanceof $this->_type) || gettype($value) == $this->_type)
			return parent::add($key, $value);

		throw new TypeMismatchException("Can't add an item with type '" . gettype($value) . "' to a Map<{$this->_type}>.");
	}
}