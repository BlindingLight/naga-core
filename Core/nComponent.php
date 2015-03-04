<?php

namespace Naga\Core;

use Naga\Core\Debug\Log\iLogger;
use Naga\Core\Debug\Log\JsConsoleLogger;
use Naga\Core\Debug\Profiler;
use Naga\Core\Debug\iProfiler;
use Naga\Core\Exception;

/**
 * Class nComponent
 * Base class for ALL of the classes in Naga. It provides basic profiling, component versioning
 * and registering.
 *
 * @package Naga\Core
 * @author BlindingLight<bloodredshade@gmail.com>
 */
abstract class nComponent implements iComponent
{
	protected static $_nagaFrameworkCodeName = 'Rainbow';
	protected static $_nagaFrameworkVersion = '2.0a';

	/**
	 * @var float component version
	 */
	protected static $_componentVersion = 1.0;

	/**
	 * @var array registered components
	 */
	private $_components = array();

	/**
	 * @var iProfiler iProfiler instance
	 */
	private $_profiler;

	/**
	 * @var iLogger iLogger instance
	 */
	private $_logger;

	/**
	 * Gets the component's iLogger instance. If iLogger instance doesn't exist,
	 * creates a new JsConsoleLogger instance and returns that.
	 *
	 * @return iLogger
	 */
	public function logger()
	{
		if (empty($this->_logger))
			$this->_logger = new JsConsoleLogger(str_replace('\\', '.', get_class($this)));

		return $this->_logger;
	}

	/**
	 * Sets the iLogger instance. This way you can register your implementation of iLogger
	 * interface.
	 *
	 * @param iLogger $logger
	 */
	public function setLogger(iLogger $logger)
	{
		$this->_logger = $logger;
	}

	/**
	 * Gets the component's iProfiler instance. If iProfiler instance doesn't exist,
	 * creates a new Profiler instance and returns that.
	 *
	 * @return iProfiler
	 */
	public function profiler()
	{
		if (empty($this->_profiler))
			$this->_profiler = new Profiler(get_class($this));

		return $this->_profiler;
	}

	/**
	 * Sets the iProfiler instance. This way you can register your implementation of iProfiler
	 * interface.
	 *
	 * @param iProfiler $profiler
	 */
	public function setProfiler(iProfiler $profiler)
	{
		$this->_profiler = $profiler;
	}

	/**
	 * Registers a component.
	 *
	 * @param string $name
	 * @param callable|nComponent|iComponent $component component must by callable or child of nComponent
	 * @throws Exception\Component\AlreadyRegisteredException
	 * @throws Exception\Component\InvalidException
	 */
	public function registerComponent($name, $component)
	{
		if (isset($this->_components[$name]))
			throw new Exception\Component\AlreadyRegisteredException("Component {$name} already registered.");

		if (!($component instanceof nComponent) && !($component instanceof iComponent) && !is_callable($component))
		{
			throw new Exception\Component\InvalidException("Can't register invalid component {$name} ("
				. gettype($component) . "), must be inherited from nComponent, implementing iComponent or be a \\Callable.");
		}

		$this->_components[$name] = $component;
	}

	/**
	 * Determines whether a component is registered or not.
	 *
	 * @param string $name
	 * @return bool
	 */
	public function componentRegistered($name)
	{
		return isset($this->_components[$name]);
	}

	/**
	 * Gets a component.
	 *
	 * @param $name
	 * @return nComponent|\Closure|iComponent
	 * @throws Exception\Component\NotFoundException
	 */
	public function component($name)
	{
		if (!isset($this->_components[$name]))
			throw new Exception\Component\NotFoundException("Component $name not found.");

		return $this->_components[$name];
	}

	/**
	 * Gets registered components. (recursive)
	 * Item format:
	 * <ul>
	 * 		<li><b>name:</b> component name (alias)			<i>string</i></li>
	 * 		<li><b>isCallable:</b> component is callable?		<i>bool</i></li>
	 *		<li><b>type:</b> component type					<i>string</i></li>
	 * 		<li><b>class:</b> component's class				<i>string</i></li>
	 * 		<li><b>version:</b> component version				<i>int</i></li>
	 * 		<li><b>instance:</b> component instance				<i>int</i></li>
	 * 		<li><b>components:</b> components				<i>array</i></li>
	 * </ul>
	 *
	 * @return array
	 */
	public function registeredComponentsRecursive()
	{
		$components = array();
		foreach ($this->_components as $name => $component)
		{
			$components[$name] = (object)array(
				'name' => $name,
				'isCallable' => is_callable($component),
				'type' => gettype($component),
				'class' => is_object($component) ? get_class($component) : '',
				'version' => $component instanceof nComponent ? $component->getComponentVersion() : '',
				'instance' => $component,
				'components' => $component instanceof nComponent ? $component->registeredComponentsRecursive() : array()
			);
		}

		return $components;
	}

	/**
	 * Echoes registered components in json format (json__encode). (recursive)
	 */
	public function registeredComponentsRecursiveJson()
	{
		echo json_encode($this->registeredComponentsRecursive());
	}

	/**
	 * Dumps registered components with var_dump(). (recursive)
	 */
	public function dumpRegisteredComponentsRecursive()
	{
		var_dump($this->registeredComponentsRecursive());
	}

	/**
	 * Gets registered components.
	 * Item format:
	 * <ul>
	 * 		<li><b>name:</b> component name (alias)			<i>string</i></li>
	 * 		<li><b>isCallable:</b> component is callable?		<i>bool</i></li>
	 *		<li><b>type:</b> component type					<i>string</i></li>
	 * 		<li><b>class:</b> component's class				<i>string</i></li>
	 * 		<li><b>version:</b> component version				<i>int</i></li>
	 * 		<li><b>instance:</b> component instance				<i>int</i></li>
	 * </ul>
	 *
	 * @return array
	 */
	public function registeredComponents()
	{
		$components = array();
		foreach ($this->_components as $name => $component)
		{
			$components[$name] = (object)array(
				'name' => $name,
				'isCallable' => is_callable($component),
				'type' => gettype($component),
				'class' => is_object($component) ? get_class($component) : '',
				'version' => $component instanceof nComponent ? $component->getComponentVersion() : '',
				'instance' => $component
			);
		}

		return $components;
	}

	/**
	 * Echoes registered components in json format (json__encode).
	 */
	public function registeredComponentsJson()
	{
		echo json_encode($this->registeredComponents());
	}

	/**
	 * Dumps registered components with var_dump().
	 */
	public function dumpRegisteredComponents()
	{
		var_dump($this->registeredComponents());
	}

	/**
	 * Gets the component's version.
	 *
	 * @return float
	 */
	public static function getComponentVersion()
	{
		return static::$_componentVersion;
	}
}