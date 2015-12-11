<?php

namespace Naga\Core;

use Naga\Core\Exception;

/**
 * Autoloader.
 *
 * @package Naga\Core
 * @author BlindingLight<bloodredshade@gmail.com>
 */
class Autoloader extends nComponent
{
	protected static $_componentVersion = 1.2;

	/**
	 * @var array External class mappings
	 */
	private $_externalClasses = [];
	/**
	 * @var array External class resolver callable functions
	 */
	private $_externalResolvers = [];
	/**
	 * @var string Root directory, where autoloader can find files
	 */
	private $_rootDirectory = '';

	/**
	 * Sets the root directory. (relative)
	 *
	 * @param string $rootDirectory
	 */
	public function setRootDirectory(\string $rootDirectory)
	{
		$this->_rootDirectory = "{$rootDirectory}/";
	}

	/**
	 * Adds external class mappings. Expects an array in format array('className' => 'filePath', ...)
	 *
	 * @param array $externalClasses
	 * @throws Exception\ArgumentMismatchException
	 */
	public function addExternalClasses(array $externalClasses)
	{
		if (!is_array($externalClasses))
			throw new Exception\ArgumentMismatchException("Couldn't add external classes, argument isn't an array");

		$this->_externalClasses = array_merge($this->_externalClasses, $externalClasses);
	}

	/**
	 * Adds an external class mapping.
	 *
	 * @param string $className
	 * @param string $filePath
	 */
	public function addExternalClass(\string $className, \string $filePath)
	{
		$this->_externalClasses[$className] = $filePath;
	}

	/**
	 * Adds an external class resolver. Autoloader calls $callable when class names
	 * start with $startString.
	 *
	 * Example:
	 * $startString = 'Test'
	 * then TestSomething, TestAnother, TestBlah will match
	 *
	 * @param string $startString
	 * @param callable $callable
	 */
	public function addExternalResolver(\string $startString, callable $callable)
	{
		$this->_externalResolvers[$startString] = $callable;
	}

	/**
	 * Adds external class resolvers. Expects an array in format array('startString' => callable, ...)
	 *
	 * @param $resolvers
	 * @throws Exception\ArgumentMismatchException
	 */
	public function addExternalResolvers(array $resolvers)
	{
		if (!is_array($resolvers))
			throw new Exception\ArgumentMismatchException("Couldn't add external resolvers, argument isn't an array");

		$this->_externalResolvers = array_merge($this->_externalResolvers, $resolvers);
	}

	/**
	 * Loads a class (require_once).
	 *
	 * @param string $className
	 * @throws Exception\AutoloadException
	 */
	public function autoload(\string $className)
	{
		$nagaPos = strpos($className, 'Naga');
		if ($nagaPos === 0 || $nagaPos === 1)
			return;

		$filePath = $this->getExternalFileName($className);
		$filePath = $this->_rootDirectory . ($filePath ? $filePath : $this->getPackageFileName($className));
		$filePath = str_replace('//', '/', str_replace('\\', '/', $filePath));
		if (!file_exists($filePath))
			throw new Exception\AutoloadException("Couldn't find class {$className} ({$filePath})");

		require_once $filePath;
	}

	/**
	 * Gets an internal class file name.
	 *
	 * @param string $className
	 * @return string
	 * @deprecated
	 */
	protected function getInternalFileName(\string $className): \string
	{
		$tmp = explode('\\', $className);
		// remove Naga namespace
		array_shift($tmp);

		$namespace = '';
		$class = array_pop($tmp);

		foreach (array_values($tmp) as $namespaceName)
			$namespace .= "{$namespaceName}\\";

		return strtolower($namespace) . "{$class}.php";
	}

	/**
	 * Gets an external class file name.
	 *
	 * @param string $className
	 * @return string
	 */
	protected function getExternalFileName(\string $className): \string
	{
		if (isset($this->_externalClasses[$className]))
			return $this->_externalClasses[$className];

		foreach ($this->_externalResolvers as $startString => $callable)
		{
			if (strpos($className, $startString) === 0 && is_callable($callable))
			{
				$filePath = '/vendor/' . $callable($className);
				if (file_exists("{$this->_rootDirectory}{$filePath}"))
					return $filePath;
			}
		}

		return '';
	}

	/**
	 * Gets a package class file name. This method will be used for app (that's a package too) classes or any other
	 * packages.
	 *
	 * @param string $className
	 * @return string
	 */
	protected function getPackageFileName(\string $className): \string
	{
		$tmp = explode('\\', $className);
		$class = array_pop($tmp);

		$namespace = '';
		foreach (array_values($tmp) as $namespaceName)
			$namespace .= "{$namespaceName}\\";

		return strtolower($namespace) . "{$class}.php";
	}
}