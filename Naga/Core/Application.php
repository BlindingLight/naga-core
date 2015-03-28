<?php

namespace Naga\Core;

use Naga\Core\Auth\Auth;
use \Naga\Core\Exception;

/**
 * Base class for your application.
 *
 * @author BlindingLight<bloodredshade@gmail.com>
 * @package Naga\Core
 */
abstract class Application extends nComponent
{
	/**
	 * @var Application[] Application instance list
	 */
	private static $_instances = array();

	/**
	 * @var string default instance name
	 */
	private static $_defaultInstanceName = 'default';

	/**
	 * @var string instance name
	 */
	private $_instanceName;

	/**
	 * Construct. DON'T overwrite it, or use parent::__construct() if you want the created instance
	 * to appear in $_instances list.
	 *
	 * @param string|null $name created instance name
	 */
	public function __construct($name = null)
	{
		$this->_instanceName = !count(self::$_instances)
			? self::$_defaultInstanceName
			: ($name ? $name : str_replace(__NAMESPACE__, '', __CLASS__));

		self::registerInstance($this);
	}

	/**
	 * Registers an instance in the $_instances list. If name is taken, throws an \Exception.
	 *
	 * @param Application $instance
	 * @param null|string $name
	 * @throws \Exception
	 */
	public static function registerInstance(Application $instance, $name = null)
	{
		$name = $name ? $name : $instance->instanceName();
		if (isset(self::$_instances[$name]))
			throw new \Exception("Can't register instance with name {$name}, name taken.");

		self::$_instances[$name] = $instance;
	}

	/**
	 * Returns an instance of this class with the specified name. Ideal for accessing components statically.
	 * If there is no instance with $name, throws an \Exception.
	 *
	 * @param string $name instance name
	 * @return $this
	 * @throws \Exception
	 */
	public static function instance($name = null)
	{
		$name = $name ? $name : self::$_defaultInstanceName;
		if (!isset(self::$_instances[$name]))
			throw new \Exception("Can't get Application instance with name {$name}.");

		return self::$_instances[$name];
	}

	/**
	 * Gets the instance's name.
	 *
	 * @return string
	 */
	public function instanceName()
	{
		return $this->_instanceName;
	}

	/**
	 * Gets a component.
	 *
	 * @param string $name
	 * @return callable|nComponent
	 */
	public function __get($name)
	{
		return $this->component($name);
	}

	/**
	 * Sets a component.
	 *
	 * @param string $name
	 * @param callable|nComponent $value
	 */
	public function __set($name, $value)
	{
		$this->registerComponent($name, $value);
	}

	/**
	 * Gets a component. You may add methods to access your components for auto-completion in IDEs.
	 *
	 * @param string $name
	 * @param array $args
	 * @return mixed
	 * @throws Exception\Component\NotCallableException
	 */
	public function __call($name, $args)
	{
		$component = $this->component($name);
		if (is_callable($component))
			return call_user_func_array($component, $args);
		else
			throw new Exception\Component\NotCallableException("Component $name (" . gettype($component) . ") is not callable.");
	}

	/**
	 * Gets a component statically. You may add methods to access your components for auto-completing in IDEs.
	 *
	 * @param string $name
	 * @param array $args
	 * @return mixed|nComponent
	 * @throws \RuntimeException
	 * @throws Exception\Component\NotCallableException
	 */
	public static function __callStatic($name, $args)
	{
		if (self::instance())
		{
			$component = self::instance()->component($name);
			if ($component instanceof nComponent)
				return $component;
			else if (is_callable($component))
				return call_user_func_array($component, $args);
			else
				throw new Exception\Component\NotCallableException("Component $name (" . gettype($component) . ") is not callable or not an instance of nComponent.");
		}

		throw new \RuntimeException("Can't get Application instance.");
	}

	/**
	 * Gets Validator instance.
	 *
	 * @return \Naga\Core\Validation\Validator
	 * @throws \Exception
	 */
	public static function validator()
	{
		try
		{
			return self::instance()->component('validator');
		}
		catch (\Exception $e)
		{
			throw new \Exception("Can't get Validator instance.");
		}
	}

	/**
	 * Gets an iDatabaseConnection instance. If $connectionName is null, gets the DatabaseManager instance.
	 *
	 * @param string|null $connectionName
	 * @return \Naga\Core\Database\Connection\iDatabaseConnection|\Naga\Core\Database\Connection\CacheableDatabaseConnection|\Naga\Core\Database\DatabaseManager
	 * @throws \RuntimeException
	 */
	public static function database($connectionName = 'default')
	{
		try
		{
			if ($connectionName)
				return self::instance()->component('database')->get($connectionName);

			return self::instance()->component('database');
		}
		catch (Exception\Component\NotFoundException $e)
		{
			throw new \RuntimeException("Can't get connection '$connectionName', missing DatabaseManager instance.");
		}
	}

	/**
	 * Gets the app's Request instance.
	 *
	 * @return \Naga\Core\Request\Request
	 * @throws \RuntimeException
	 */
	public static function request()
	{
		try
		{
			return self::instance()->component('request');
		}
		catch (Exception\Component\NotFoundException $e)
		{
			throw new \RuntimeException("Can't get Request instance.");
		}
	}

	/**
	 * Gets the app's Input instance.
	 *
	 * @return \Naga\Core\Request\Input
	 * @throws \RuntimeException
	 */
	public static function input()
	{
		try
		{
			return self::instance()->component('input');
		}
		catch (Exception\Component\NotFoundException $e)
		{
			throw new \RuntimeException("Can't get Input instance.");
		}
	}

	/**
	 * Gets the app's Cookie instance.
	 *
	 * @return \Naga\Core\Cookie\Cookie
	 * @throws \RuntimeException
	 */
	public static function cookie()
	{
		try
		{
			return self::instance()->component('cookie');
		}
		catch (Exception\Component\NotFoundException $e)
		{
			throw new \RuntimeException("Can't get Cookie instance.");
		}
	}

	/**
	 * Gets the app's SecureCookie instance.
	 *
	 * @return \Naga\Core\Cookie\SecureCookie
	 * @throws \RuntimeException
	 */
	public static function secureCookie()
	{
		try
		{
			return self::instance()->component('securecookie');
		}
		catch (Exception\Component\NotFoundException $e)
		{
			throw new \RuntimeException("Can't get SecureCookie instance.");
		}
	}

	/**
	 * Gets the app's Router instance.
	 *
	 * @return \Naga\Core\Routing\Router
	 * @throws \RuntimeException
	 */
	public static function router()
	{
		try
		{
			return self::instance()->component('router');
		}
		catch (Exception\Component\NotFoundException $e)
		{
			throw new \RuntimeException("Can't get Router instance.");
		}
	}

	/**
	 * Gets the app's SessionManager instance.
	 *
	 * @return \Naga\Core\Session\SessionManager
	 * @throws \RuntimeException
	 */
	public static function session()
	{
		try
		{
			return self::instance()->component('session');
		}
		catch (Exception\Component\NotFoundException $e)
		{
			throw new \RuntimeException("Can't get SessionManager instance.");
		}
	}

	/**
	 * Gets the app's iFileSystem instance.
	 *
	 * @return \Naga\Core\FileSystem\iFileSystem
	 * @throws \RuntimeException
	 */
	public static function fileSystem()
	{
		try
		{
			return self::instance()->component('fileSystem');
		}
		catch (Exception\Component\NotFoundException $e)
		{
			throw new \RuntimeException("Can't get iFileSystem instance.");
		}
	}

	/**
	 * Gets the app's Config instance.
	 * If no ConfigBag name specified gets 'application' ConfigBag instance.
	 * You can access config properties like directory.configBagName::property.subProperty
	 *
	 * @param string $configName if you want to get a ConfigBag or property directly, specify it's name here
	 * @return \Naga\Core\Config\Config|\Naga\Core\Config\ConfigBag
	 * @throws \RuntimeException
	 */
	public static function config($configName = null)
	{
		try
		{
			$config = self::instance()->component('config');

			return $configName === null ? $config : $config($configName);
		}
		catch (Exception\Component\NotFoundException $e)
		{
			throw new \RuntimeException("Can't get Config instance.");
		}
	}

	/**
	 * Gets the app's CacheManager instance.
	 *
	 * @return \Naga\Core\Cache\CacheManager
	 * @throws \RuntimeException
	 */
	public static function cache()
	{
		try
		{
			return self::instance()->component('cache');
		}
		catch (Exception\Component\NotFoundException $e)
		{
			throw new \RuntimeException("Can't get CacheManager instance.");
		}
	}

	/**
	 * Gets the app's Auth instance.
	 *
	 * @return \Naga\Core\Auth\Auth
	 * @throws \RuntimeException
	 */
	public static function auth()
	{
		try
		{
			return self::instance()->component('auth');
		}
		catch (Exception\Component\NotFoundException $e)
		{
			throw new \RuntimeException("Can't get Auth instance.");
		}
	}

	/**
	 * Gets the app's UrlGenerator instance.
	 *
	 * @return \Naga\Core\Routing\UrlGenerator
	 * @throws \RuntimeException
	 */
	public static function urlGenerator()
	{
		try
		{
			return self::instance()->component('urlgenerator');
		}
		catch (Exception\Component\NotFoundException $e)
		{
			throw new \RuntimeException("Can't get UrlGenerator instance.");
		}
	}

	/**
	 * Gets the app's Localization instance.
	 *
	 * @return \Naga\Core\Localization\Localization
	 * @throws \RuntimeException
	 */
	public static function localization()
	{
		try
		{
			return self::instance()->component('localization');
		}
		catch (Exception\Component\NotFoundException $e)
		{
			throw new \RuntimeException("Can't get Localization instance.");
		}
	}

	/**
	 * Gets the app's Email instance.
	 *
	 * @return \Naga\Core\Email\Email
	 * @throws \RuntimeException
	 */
	public static function email()
	{
		try
		{
			return self::instance()->component('email');
		}
		catch (Exception\Component\NotFoundException $e)
		{
			throw new \RuntimeException("Can't get Email instance.");
		}
	}

	/**
	 * Gets the app's Events instance.
	 *
	 * @return \Naga\Core\Event\Events
	 * @throws \RuntimeException
	 */
	public static function events()
	{
		try
		{
			return self::instance()->component('events');
		}
		catch (Exception\Component\NotFoundException $e)
		{
			throw new \RuntimeException("Can't get Events instance.");
		}
	}

	/**
	 * Gets the app's Hasher instance.
	 *
	 * @return \Naga\Core\Hashing\Hasher
	 * @throws \RuntimeException
	 */
	public static function hasher()
	{
		try
		{
			return self::instance()->component('hasher');
		}
		catch (Exception\Component\NotFoundException $e)
		{
			throw new \RuntimeException("Can't get Hasher instance.");
		}
	}

	/**
	 * Gets the app's iQueryBuilder instance.
	 *
	 * @return \Naga\Core\Database\iQueryBuilder
	 * @throws \RuntimeException
	 */
	public static function queryBuilder()
	{
		try
		{
			return self::instance()->component('queryBuilder');
		}
		catch (Exception\Component\NotFoundException $e)
		{
			throw new \RuntimeException("Can't get iQueryBuilder instance.");
		}
	}

	/**
	 * Redirects the user to the specified url.
	 *
	 * @param string $url
	 * @param int $statusCode http status code
	 * @deprecated
	 */
	public function redirect($url = '/', $statusCode = 302)
	{
		static::redirectTo($url, $statusCode, $this->instanceName());
	}

	/**
	 * Redirects the user to the specified url.
	 * You can use routes here:
	 *  '@routename;param1:value1|param2:value2'
	 *
	 * @param string $url
	 * @param int    $statusCode
	 * @param null   $appInstance
	 * @throws \Exception
	 */
	public static function redirectTo($url = '/', $statusCode = 302, $appInstance = null)
	{
		$instance = !is_null($appInstance) ? self::instance($appInstance) : self::instance();
		$instance->finish();

		if (strpos($url, '@') !== false)
		{
			$tmp = explode(';', $url);
			$params = '';
			if (isset($tmp[1]))
				$params = $tmp[1];

			$url = $instance->urlGenerator()->route(str_replace('@', '', $tmp[0]), $params);
		}

		http_response_code($statusCode);
		header('Location: ' . $url);

		exit;
	}

	/**
	 * Sets response http status code.
	 *
	 * @param int $statusCode
	 */
	public static function setResponseStatusCode($statusCode)
	{
		http_response_code($statusCode);
	}

	/**
	 * Ending tasks, like store auth data in session, etc.
	 */
	public function finish()
	{
		if (isset($this->auth) && $this->auth instanceof Auth)
			$this->auth->storeSessionData();
	}

	/**
	 * Application logic.
	 */
	public function run()
	{

	}

	/**
	 * Performs initialization tasks.
	 */
	public function init()
	{

	}
}