<?php

namespace Naga\Core\Module;

use Naga\Core\Application;
use Naga\Core\Event\iSubscriber;
use Naga\Core\nComponent;

/**
 * Abstract class for creating modules.
 *
 * You can add event listeners in subscribe().
 * See Event class documentation.
 *
 * Installing a module:
 * 1. Place code in app/module/modulename directory
 * 2. Instantiate module in app/bootstrap.php or basically anywhere
 * $app->moduleName = new ModuleName($app);
 * 3. In order to use autocomplete in IDEs, add a static method
 * to your App instance. If you do so, you can access
 * module instance like $app->moduleName()->something() or
 * App::moduleName()->something() otherwise
 * App::component('moduleName')->something
 * or create a Facade
 *
 * @package Naga\Core\Module
 * @author  BlindingLight<bloodredshade@gmail.com>
 */
abstract class Module extends nComponent implements iSubscriber
{
	/**
	 * @var float module version
	 */
	private static $_version = 1.0;

	/**
	 * @var Application module's Application instance
	 */
	private static $_app;

	/**
	 * Construct.
	 *
	 * @param array $settings
	 */
	abstract function __construct($settings = array());

	/**
	 * Initializes the module. You may override this method if
	 * you want to do other initialization tasks, but you have to
	 * call parent::init() first in order to access created instance(s).
	 * You can access this instances via App::component($componentName)
	 * or $this->instance($componentName). This way you can create
	 * multiple instances and change them.
	 *
	 * Default behavior:
	 * 1. Registers a new module instance to App as component
	 * 2. Subscribes to events
	 *
	 * @param string $componentName
	 * @param Application $app
	 * @param array $settings
	 */
	public static function init($componentName, Application $app, $settings = array())
	{
		$className = __CLASS__;
		$app->registerComponent($componentName, new $className($settings));
		$app->events()->subscribe($app->component($componentName));
		self::$_app = $app;
	}

	/**
	 * Gets module instance with given name.
	 *
	 * @param string $componentName
	 * @return $this
	 * @throws \RuntimeException
	 */
	public static function instance($componentName)
	{
		try
		{
			return self::$_app->component($componentName);
		}
		catch (\Exception $e)
		{
			throw new \RuntimeException("Can't get module instance {$componentName}.");
		}
	}

	/**
	 * Sets module version.
	 *
	 * @param float $version
	 */
	protected static function setVersion($version)
	{
		if (!is_numeric($version))
			self::$_version = 1.0;

		self::$_version = (float)$version;
	}

	/**
	 * Gets module version.
	 *
	 * @return float
	 * @throws \RuntimeException
	 */
	public static function version()
	{
		try
		{
			return self::$_version;
		}
		catch (\Exception $e)
		{
			throw new \RuntimeException('Can\'t get module\'s Application instance.');
		}
	}

	/**
	 * Gets App instance.
	 *
	 * @return Application
	 */
	protected  static function app()
	{
		return self::$_app;
	}

	/**
	 * Subscribes to events.
	 */
	abstract function subscribe();
}