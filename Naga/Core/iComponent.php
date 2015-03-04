<?php

namespace Naga\Core;

interface iComponent
{
	/**
	 * Registers a component.
	 *
	 * @param string $name
	 * @param callable|nComponent|iComponent $component component must by callable or child of iComponent
	 * @throws Exception\Component\AlreadyRegisteredException
	 * @throws Exception\Component\InvalidException
	 */
	public function registerComponent($name, $component);

	/**
	 * Determines whether a component is registered or not.
	 *
	 * @param string $name
	 * @return bool
	 */
	public function componentRegistered($name);

	/**
	 * Gets a component.
	 *
	 * @param $name
	 * @return nComponent|\Closure|iComponent
	 * @throws Exception\Component\NotFoundException
	 */
	public function component($name);

	/**
	 * Gets the registered components. (recursive)
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
	public function registeredComponentsRecursive();

	/**
	 * Echoes the registered components in json format (json__encode). (recursive)
	 */
	public function registeredComponentsRecursiveJson();

	/**
	 * Dumps the registered components with var_dump(). (recursive)
	 */
	public function dumpRegisteredComponentsRecursive();

	/**
	 * Gets the registered components.
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
	public function registeredComponents();

	/**
	 * Echoes the registered components in json format (json__encode).
	 */
	public function registeredComponentsJson();

	/**
	 * Dumps the registered components with var_dump().
	 */
	public function dumpRegisteredComponents();

	/**
	 * Gets the component's version.
	 *
	 * @return float
	 */
	public static function getComponentVersion();
}