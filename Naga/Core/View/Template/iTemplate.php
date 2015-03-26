<?php

namespace Naga\Core\View\Template;

interface iTemplate
{
	/**
	 * Generates template output.
	 *
	 * @param string|null $templatePath override template path
	 * @return string
	 * @throws \Naga\Core\Exception\ConfigException
	 */
	function generate($templatePath = null);

	/**
	 * Assigns a variable that is accessible from template files.
	 *
	 * @param string $name
	 * @param mixed $value
	 */
	function assign($name, $value);

	/**
	 * Alias of assign().
	 *
	 * @param string $name
	 * @param mixed $value
	 */
	function set($name, $value);

	/**
	 * Gets an assigned variable.
	 *
	 * @param string $name
	 * @param null $default
	 * @return null|mixed
	 */
	function get($name, $default = null);

	/**
	 * Sets template path.
	 *
	 * @param string $path
	 */
	function setTemplatePath($path);
}