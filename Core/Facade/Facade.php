<?php

namespace Naga\Core\Facade;

use Naga\Core\nComponent;

abstract class Facade
{
	/**
	 * @var \Naga\Core\nComponent|\Naga\Core\Application nComponent instance being facaded
	 */
	protected static $_container;

	/**
	 * @var string component accessor
	 */
	protected static $_accessor;

	/**
	 * Sets the container that being facaded.
	 *
	 * @param nComponent $container
	 */
	public static function setContainer(nComponent $container)
	{
		static::$_container = $container;
	}

	/**
	 * Gets the container.
	 *
	 * @return \Naga\Core\Application|nComponent
	 */
	protected static function container()
	{
		return static::$_container instanceof nComponent ? static::$_container : self::$_container;
	}

	/**
	 * Gets the registered component instance from container.
	 *
	 * @return callable|nComponent
	 * @throws \RuntimeException
	 */
	protected static function component()
	{
		if (empty(static::$_accessor))
			throw new \RuntimeException(__CLASS__ . ": Can't get component from container, accessor is empty.");

		return static::container()->{static::$_accessor};
	}

	/**
	 * Calls a method on container.
	 *
	 * @param string $methodName
	 * @param array $arguments
	 * @return mixed
	 */
	public static function __callStatic($methodName, $arguments)
	{
		return call_user_func_array(array(static::$_container, $methodName), $arguments);
	}
}