<?php

declare(strict_types=1);

namespace Naga\Core;

use Naga\Core\Action\Action;
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
	private static $_instances = [];

	/**
	 * @var string default instance name
	 */
	protected static $_defaultInstanceName = 'default';

	/**
	 * @var string instance name
	 */
	private $_instanceName;

	/**
	 * Construct. DON'T overwrite it, or use parent::__construct() if you want the created instance
	 * to appear in $_instances list.
	 *
	 * @param string $name created instance name (can be empty)
	 * @throws \Exception
	 */
	public function __construct(\string $name = '')
	{
		// if no name specified, we set a default name
		if (!$name)
		{
			$name = !count(self::$_instances)
					? static::$_defaultInstanceName
					: str_replace(__NAMESPACE__, '', __CLASS__);
		}

		$this->_instanceName = $name;

		self::registerInstance($this);
	}

	/**
	 * Registers an instance in the $_instances list. If name is taken, throws an \Exception.
	 *
	 * @param Application $instance
	 * @param string $name
	 * @throws \Exception
	 */
	public static function registerInstance(Application $instance, \string $name = '')
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
	public static function instance(\string $name = ''): self
	{
		$name = $name ? $name : static::$_defaultInstanceName;
		if (!isset(self::$_instances[$name]))
			throw new \Exception("Can't get Application instance with name {$name}.");

		return self::$_instances[$name];
	}

	/**
	 * Gets the instance's name.
	 *
	 * @return string
	 */
	public function instanceName(): \string
	{
		return $this->_instanceName;
	}

	/**
	 * Gets a component.
	 *
	 * @param string $name
	 * @return nComponent
	 */
	public function __get(\string $name): nComponent
	{
		return $this->component($name);
	}

	/**
	 * Sets a component.
	 *
	 * @param string $name
	 * @param nComponent $value
	 * @throws Exception\Component\AlreadyRegisteredException
	 * @throws Exception\Component\InvalidException
	 */
	public function __set(\string $name, nComponent $value)
	{
		$this->registerComponent($name, $value);
	}

	/**
	 * Gets a component. You may add methods to access your components for auto-completion in IDEs.
	 *
	 * @param string $name
	 * @param array $args
	 * @return \Naga\Core\nComponent
	 * @throws \Naga\Core\Exception\Component\NotFoundException
	 */
	public function __call(\string $name, array $args): nComponent
	{
		return $this->component($name);
	}

	/**
	 * Gets a component statically. You may add methods to access your components for auto-completion in IDEs.
	 *
	 * @param string $name
	 * @param array $args
	 * @return \Naga\Core\nComponent
	 * @throws \Exception
	 * @throws \Naga\Core\Exception\Component\NotFoundException
	 */
	public static function __callStatic(\string $name, array $args)
	{
		if (self::instance())
			return self::instance()->component($name);

		throw new \RuntimeException("Can't get Application instance.");
	}

	/**
	 * Gets Validator instance.
	 *
	 * @return \Naga\Core\Validation\Validator
	 * @throws \Naga\Core\Exception\Component\NotFoundException
	 */
	public static function validator(): \Naga\Core\Validation\Validator
	{
		return self::instance()->component('validator');
	}

	/**
	 * Gets an iDatabaseConnection instance. If $connectionName is null, gets the DatabaseManager instance.
	 *
	 * @param string|null $connectionName
	 * @return \Naga\Core\Database\Connection\iDatabaseConnection|\Naga\Core\Database\Connection\CacheableDatabaseConnection
	 * @throws \Naga\Core\Exception\Component\NotFoundException
	 */
	public static function database($connectionName = 'default'): \Naga\Core\Database\Connection\iDatabaseConnection
	{
		return self::databaseManager()->get($connectionName);
	}

	/**
	 * @return \Naga\Core\Database\DatabaseManager
	 * @throws \Exception
	 * @throws \Naga\Core\Exception\Component\NotFoundException
	 */
	public static function databaseManager(): \Naga\Core\Database\DatabaseManager
	{
		return self::instance()->component('databaseManager');
	}

	/**
	 * Gets the app's Request instance.
	 *
	 * @return \Naga\Core\Request\Request
	 * @throws \Naga\Core\Exception\Component\NotFoundException
	 */
	public static function request(): \Naga\Core\Request\Request
	{
		return self::instance()->component('request');
	}

	/**
	 * Gets the app's Input instance.
	 *
	 * @return \Naga\Core\Request\Input
	 * @throws \Naga\Core\Exception\Component\NotFoundException
	 */
	public static function input(): \Naga\Core\Request\Input
	{
		return self::instance()->component('input');
	}

	/**
	 * Gets the app's Cookie instance.
	 *
	 * @return \Naga\Core\Cookie\Cookie
	 * @throws \Naga\Core\Exception\Component\NotFoundException
	 */
	public static function cookie(): \Naga\Core\Cookie\Cookie
	{
		return self::instance()->component('cookie');
	}

	/**
	 * Gets the app's SecureCookie instance.
	 *
	 * @return \Naga\Core\Cookie\SecureCookie
	 * @throws \Naga\Core\Exception\Component\NotFoundException
	 */
	public static function secureCookie(): \Naga\Core\Cookie\SecureCookie
	{
		return self::instance()->component('securecookie');
	}

	/**
	 * Gets the app's Router instance.
	 *
	 * @return \Naga\Core\Routing\Router
	 * @throws \Naga\Core\Exception\Component\NotFoundException
	 */
	public static function router(): \Naga\Core\Routing\Router
	{
		return self::instance()->component('router');
	}

	/**
	 * Gets the app's SessionManager instance.
	 *
	 * @return \Naga\Core\Session\SessionManager
	 * @throws \Naga\Core\Exception\Component\NotFoundException
	 */
	public static function session(): \Naga\Core\Session\SessionManager
	{
		return self::instance()->component('session');
	}

	/**
	 * Gets the app's iFileSystem instance.
	 *
	 * @return \Naga\Core\FileSystem\iFileSystem
	 * @throws \RuntimeException
	 */
	public static function fileSystem(): \Naga\Core\FileSystem\iFileSystem
	{
		return self::instance()->component('fileSystem');
	}

	/**
	 * Gets the app's Config instance.
	 * If no ConfigBag name specified gets 'application' ConfigBag instance.
	 * You can access config properties like directory.configBagName::property.subProperty
	 *
	 * @param string $configName if you want to get a ConfigBag or property directly, specify it's name here
	 * @return \Naga\Core\Config\Config|\Naga\Core\Config\ConfigBag|mixed
	 * @throws \Naga\Core\Exception\Component\NotFoundException
	 */
	public static function config(\string $configName = '')
	{
		$config = self::instance()->component('config');

		return $configName == '' ? $config : $config($configName);
	}

	/**
	 * Gets the app's CacheManager instance.
	 *
	 * @return \Naga\Core\Cache\CacheManager
	 * @throws \Naga\Core\Exception\Component\NotFoundException
	 */
	public static function cache(): \Naga\Core\Cache\CacheManager
	{
		return self::instance()->component('cache');
	}

	/**
	 * Gets the app's Auth instance.
	 *
	 * @return \Naga\Core\Auth\Auth
	 * @throws \Naga\Core\Exception\Component\NotFoundException
	 */
	public static function auth(): \Naga\Core\Auth\Auth
	{
		return self::instance()->component('auth');
	}

	/**
	 * Gets the app's UrlGenerator instance.
	 *
	 * @return \Naga\Core\Routing\UrlGenerator
	 * @throws \Naga\Core\Exception\Component\NotFoundException
	 */
	public static function urlGenerator(): \Naga\Core\Routing\UrlGenerator
	{
		return self::instance()->component('urlgenerator');
	}

	/**
	 * Gets the app's Localization instance.
	 *
	 * @return \Naga\Core\Localization\Localization
	 * @throws \Naga\Core\Exception\Component\NotFoundException
	 */
	public static function localization(): \Naga\Core\Localization\Localization
	{
		return self::instance()->component('localization');
	}

	/**
	 * Gets the app's Email instance.
	 *
	 * @return \Naga\Core\Email\Email
	 * @throws \Naga\Core\Exception\Component\NotFoundException
	 */
	public static function email(): \Naga\Core\Email\Email
	{
		return self::instance()->component('email');
	}

	/**
	 * Gets the app's Events instance.
	 *
	 * @return \Naga\Core\Event\Events
	 * @throws \Naga\Core\Exception\Component\NotFoundException
	 */
	public static function events(): \Naga\Core\Event\Events
	{
		return self::instance()->component('events');
	}

	/**
	 * Gets the app's Hasher instance.
	 *
	 * @return \Naga\Core\Hashing\Hasher
	 * @throws \Naga\Core\Exception\Component\NotFoundException
	 */
	public static function hasher(): \Naga\Core\Hashing\Hasher
	{
		return self::instance()->component('hasher');
	}

	/**
	 * Gets the app's iQueryBuilder instance.
	 *
	 * @return \Naga\Core\Database\iQueryBuilder
	 * @throws \RuntimeException
	 */
	public static function queryBuilder(): \Naga\Core\Database\iQueryBuilder
	{
		return self::instance()->component('queryBuilder');
	}

	/**
	 * Redirects the user to the specified url.
	 *
	 * @param string $url
	 * @param int $statusCode http status code
	 * @deprecated
	 */
	public function redirect(\string $url = '/', \int $statusCode = 302)
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
	 * @param string   $appInstance
	 * @throws \Exception
	 */
	public static function redirectTo(\string $url = '/', \int $statusCode = 302, \string $appInstance = '')
	{
		$instance = !is_null($appInstance) ? self::instance($appInstance) : self::instance();
		$instance->finish();

		// TODO: check this, seems buggy
		if (strpos($url, '@') !== false)
		{
			$tmp = explode(';', $url);
			$params = '';
			if (isset($tmp[1]))
				$params = $tmp[1];

			$url = $instance->urlGenerator()->route(str_replace('@', '', $tmp[0]), $params);
		}

		static::setResponseStatusCode($statusCode);
		header("Location: {$url}");

		exit;
	}

	/**
	 * Sets response http status code.
	 *
	 * @param int $statusCode
	 */
	public static function setResponseStatusCode(\int $statusCode)
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
		// logout
		if ($this->auth()->isLoggedIn() && $this->input()->exists('logout'))
		{
			$this->auth()->logout();
			static::redirectTo();
		}

		$defaultRoute = static::auth()->isLoggedIn()
						? static::config()->application->get('defaultRouteIfLoggedIn')
						: static::config()->application->get('defaultRoute');
		static::router()->setDefaultRoute($defaultRoute);
		$controllerResult = self::router()->routeUri();
		if ($controllerResult instanceof Action)
			$controllerResult->execute();
		else if (is_string($controllerResult))
			echo $controllerResult;
	}

	/**
	 * Performs initialization tasks.
	 */
	public function init()
	{

	}
}