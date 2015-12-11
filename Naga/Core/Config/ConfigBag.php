<?php

namespace Naga\Core\Config;

use Naga\Core\Collection\Map;
use Naga\Core\FileSystem\iFileSystem;

/**
 * Container class for config properties.
 *
 * @package Naga\Core\Config
 * @author  BlindingLight<bloodredshade@gmail.com>
 */
class ConfigBag extends Map
{
	/**
	 * Construct. Sets an iFileSystem class if specified.
	 *
	 * @param iFileSystem $fileSystem
	 */
	public function __construct(iFileSystem $fileSystem = null)
	{
		if ($fileSystem)
			$this->setFileSystem($fileSystem);

		parent::__construct();
	}

	/**
	 * Gets a config property if accessed like $configBag->property.
	 *
	 * @param string $name
	 * @return mixed|null
	 */
	public function __get(\string $name)
	{
		return $this->get($name);
	}

	/**
	 * Gets a config property as string.
	 *
	 * @param string $name
	 * @return string
	 */
	public function getString(\string $name): \string
	{
		return (string)$this->get($name);
	}

	/**
	 * Gets a config property as int.
	 *
	 * @param string $name
	 * @return int
	 */
	public function getInt(\string $name): \int
	{
		return (int)$this->get($name);
	}

	/**
	 * Gets a config property as float.
	 *
	 * @param string $name
	 * @return float
	 */
	public function getFloat(\string $name): \float
	{
		return (float)$this->get($name);
	}

	/**
	 * Gets a config property as double.
	 *
	 * @param string $name
	 * @return double
	 */
	public function getDouble(\string $name): \double
	{
		return (double)$this->get($name);
	}

	/**
	 * Gets a config property as boolean.
	 *
	 * @param string $name
	 * @return bool
	 */
	public function getBool(\string $name): \bool
	{
		if ($name == 'true')
			return true;
		if ($name == 'false')
			return false;

		return (bool)$this->get($name);
	}

	/**
	 * Gets a config property as an array.
	 *
	 * @param string $name
	 * @return array
	 */
	public function getArray(\string $name): array
	{
		return (array)$this->get($name);
	}

	/**
	 * Gets a config property as an object.
	 *
	 * @param string $name
	 * @return object
	 */
	public function getObject(\string $name): \object
	{
		return (object)$this->get($name);
	}

	/**
	 * Sets the iFileSystem instance.
	 *
	 * @param iFileSystem $fileSystem
	 */
	public function setFileSystem(iFileSystem $fileSystem)
	{
		$this->registerComponent('fileSystem', $fileSystem);
	}

	/**
	 * Gets the iFileSystem instance.
	 *
	 * @return \Naga\Core\FileSystem\iFileSystem
	 */
	protected function fileSystem()
	{
		return $this->component('fileSystem');
	}

	/**
	 * Gets a config array from a file. Returns the ConfigBag instance. File must be php,
	 * and it must return an array.
	 *
	 * @param string $filePath
	 * @return $this
	 */
	public function getFile($filePath)
	{
		return $this->copyFrom($this->fileSystem()->getRequire($filePath));
	}

	public function getJsonFile($filePath)
	{
		$this->mergeWith((array)json_decode($this->fileSystem()->get($filePath)));
	}
}