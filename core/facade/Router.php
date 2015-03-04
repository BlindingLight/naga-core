<?php

namespace Naga\Core\Facade;

use Naga\Core\Exception;

class Router extends Facade
{
	/**
	 * @see Facade
	 */
	protected static $_accessor = 'router';

	/**
	 * @see \Naga\Core\Routing\Router
	 */
	public static function setDefaultRoute($routeName)
	{
		return static::component()->setDefaultRoute($routeName);
	}

	/**
	 * @see \Naga\Core\Routing\Router
	 */
	public static function defaultRouteName()
	{
		return static::component()->defaultRouteName();
	}

	/**
	 * @see \Naga\Core\Routing\Router
	 */
	public static function routeUri()
	{
		return static::component()->routeUri();
	}

	/**
	 * @see \Naga\Core\Routing\Router
	 */
	public static function addRoute($mappedUrl, $route)
	{
		return static::component()->addRoute($mappedUrl, $route);
	}

	/**
	 * @see \Naga\Core\Routing\Router
	 */
	public static function addRoutes(array $routes)
	{
		return static::component()->addRoutes($routes);
	}
}