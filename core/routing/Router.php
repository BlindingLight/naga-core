<?php

namespace Naga\Core\Routing;

use Naga\Core\Request\Request;
use Naga\Core\nComponent;
use Naga\Core\Exception;

/**
 * Routes the request uri.
 *
 * @author BlindingLight<bloodredshade@gmail.com>
 * @package Naga\Core\Routing
 */
class Router extends nComponent
{
	/**
	 * @var array
	 */
	private $_routes = array();
	/**
	 * @var array url -> route mappings
	 */
	private $_urlMappings = array();
	/**
	 * @var string
	 */
	private $_defaultRoute = '/';
	/**
	 * @var string
	 */
	private $_matchedMappedUrl;
	/**
	 * @var string used if site is running in a directory, eg example.com/site
	 */
	private $_basePath = '';

	/**
	 * Construct.
	 *
	 * @param Request $request
	 * @param string $basePath
	 */
	public function __construct(Request $request, $basePath = '')
	{
		$this->registerComponent('request', $request);
		$this->_basePath = $basePath;
	}

	/**
	 * Sets the default route. This will be used if no match found.
	 *
	 * @param string $routeName
	 * @throws Exception\Routing\RouteNotFoundException
	 */
	public function setDefaultRoute($routeName)
	{
		if (!isset($this->_routes[$routeName]))
			throw new Exception\Routing\RouteNotFoundException("Can't set default route '{$routeName}', doesn't exist.");

		$this->_defaultRoute = $routeName;
	}

	/**
	 * Gets the default route name.
	 *
	 * @return string
	 */
	public function defaultRouteName()
	{
		return $this->_defaultRoute;
	}

	/**
	 * Routes the request uri. And returns the executed route function/method result.
	 *
	 * @return \Naga\Core\Action\Action|mixed
	 * @throws Exception\Routing\RouteBadlyConfiguredException
	 */
	public function routeUri()
	{
		$this->profiler()->createTimer('routeUri');

		$route = $this->matchUri($this->uri());
		$route->parameters = $this->getParameters($this->_matchedMappedUrl, $route->parameters);
		$route->method = $this->request()->httpMethodString();

		if (isset($route->callable) && is_callable($route->callable))
			return call_user_func_array($route->callable, array_merge(array($route->method), $route->parameters));

		if (!isset($route->{$route->method}))
			$route->method = 'get';

		if (!isset($route->{$route->method}))
		{
			$this->profiler()->stopTimer('routeUri');
			throw new Exception\Routing\RouteBadlyConfiguredException("Badly configured route, missing 'get'.");
		}

		if (is_callable($route->{$route->method}))
		{
			$this->profiler()->stopTimer('routeUri');
			return call_user_func_array($route->{$route->method}, $route->parameters);
		}
		else
		{
			list($className, $methodName) = explode('@', $route->{$route->method});
			try
			{
				$controller = new $className();
				$this->profiler()->stopTimer('routeUri');
				return $controller->{$methodName}($route->parameters);
			}
			catch (\Exception $e)
			{
				$this->profiler()->stopTimer('routeUri');
				return $route->{$route->method} . ":\n" . $e->getMessage();
			}
		}
	}

	/**
	 * Creates an associative array of parameters for the given mapped url from the $parameters array.
	 *
	 * @param string $mappedUrl
	 * @param array $parameters an array containing parameter values, must be number indexed
	 * @return array
	 */
	protected function getParameters($mappedUrl, $parameters)
	{
		if (!$mappedUrl || $mappedUrl == '/')
			return array();

		$this->profiler()->createTimer('getParameters');
		$parts = explode('/', $mappedUrl);
		$finalized = array();
		foreach ($parts as $part)
		{
			if (preg_match('/{[a-zA-Z0-9-_]+\|.+}/', $part))
				$finalized[preg_replace('/{([a-zA-Z0-9]+)\|(.+)}/', '$1', $part)] = array_shift($parameters);
		}

		$this->profiler()->stopTimer('getParameters');
		return $finalized;
	}

	/**
	 * Matches the request uri and returns an object with route data.
	 *
	 * @param string $uri
	 * @return object
	 */
	protected function matchUri($uri)
	{
		$this->profiler()->createTimer('matchUri');
		if (empty($uri))
			$uri = '/';
		$uri = $uri != '/' ? trim($uri, '/') : $uri;
		foreach ($this->_urlMappings as $mappedUrl => $routeName)
		{
			$pattern = $this->createRegexFromMappedUrl($mappedUrl);
			$matches = array();
			if (preg_match($pattern, $uri, $matches))
			{
				if (is_callable($this->_routes[$routeName]))
				{
					$matches['rootUriPart'] = array_shift($matches);
					$this->_matchedMappedUrl = $mappedUrl;
					$this->profiler()->stopTimer('matchUri');
					return (object)array(
						'callable' => $this->_routes[$routeName],
						'parameters' => $matches
					);
				}
				else if (isset($this->_routes[$routeName]->domain)
					&& !$this->domainCheck($this->request()->domainName(), $this->_routes[$routeName]->domain))
				{
					continue;
				}

				$matches['rootUriPart'] = array_shift($matches);
				$this->_routes[$routeName]->parameters = $matches;
				$this->_matchedMappedUrl = $mappedUrl;
				$this->profiler()->stopTimer('matchUri');
				return $this->_routes[$routeName];
			}
		}

		$route = $this->_routes[$this->_defaultRoute];

		if (is_callable($route))
		{
			$this->profiler()->stopTimer('matchUri');
			return (object)array(
				'callable' => $route,
				'parameters' => array()
			);
		}

		$route->parameters = array();
		$this->profiler()->stopTimer('matchUri');
		return $route;
	}

	/**
	 * Checks whether the current request domain is valid for the route.
	 *
	 * @param string $domain current domain
	 * @param string $expected expected domain (route domain)
	 * @return bool
	 */
	protected function domainCheck($domain, $expected)
	{
		$this->profiler()->createTimer('domainCheck');
		$domainParts = explode('.', $domain);
		$expectedParts = explode('.', $expected);
		$lastExpectedPart = $expectedParts[count($expectedParts) - 1];
		$domainPartsCount = count($domainParts);
		$expectedPartsCount = count($expectedParts);

		if ($domainPartsCount < $expectedPartsCount)
		{
			$this->profiler()->stopTimer('domainCheck');
			return false;
		}

		if ($lastExpectedPart != '*' && $domainPartsCount != $expectedPartsCount)
		{
			$this->profiler()->stopTimer('domainCheck');
			return false;
		}
		else if ($lastExpectedPart == '*' && $expectedPartsCount < $domainPartsCount)
		{
			$this->profiler()->stopTimer('domainCheck');
			return true;
		}

		foreach ($domainParts as $idx => $domainPart)
		{
			$expectedPart = $expectedParts[$idx];
			if ($expectedPart != '*' && $domainPart != $expectedPart)
			{
				$this->profiler()->stopTimer('domainCheck');
				return false;
			}
		}

		return true;
	}

	/**
	 * Creates a regex pattern from a mapped url. Used for matching url -> mapped url.
	 *
	 * @param string $mappedUrl
	 * @return string
	 */
	protected function createRegexFromMappedUrl($mappedUrl)
	{
		if (!$mappedUrl || $mappedUrl == '/')
			return '#^/$#';

		$this->profiler()->createTimer('createRegexFromMappedUrl');
		// we check if there is only one uri part cause we can get some performance improvement this way
		if (strpos($mappedUrl, '/') === false)
		{
			$this->profiler()->stopTimer('createRegexFromMappedUrl');
			return "/^{$mappedUrl}\$/";
		}

		$parts = explode('/', $mappedUrl);
		$regex = '/^';
		foreach ($parts as $idx => $part)
		{
			$regex .= !$idx ? '' : '\/';
			// if regex pattern is specified in part
			if (preg_match('/{[^\|]+?\|.+}/', $part))
				$regex .= '(' . preg_replace('/{([^\|]+?)\|(.+?)}/', '$2', $part) . ')';
			else
				$regex .= $part;
		}

		$this->profiler()->stopTimer('createRegexFromMappedUrl');
		return $regex . '$/';
	}

	/**
	 * Adds a route. Route must be a callable function or a string with format 'className[at]methodName'.
	 *
	 * @param string $mappedUrl
	 * @param \Callable|string $route
	 * @return $this
	 * @throws Exception\Routing\RouteAlreadyExistsException
	 */
	public function addRoute($mappedUrl, $route)
	{
		if (isset($this->_routes[$mappedUrl]))
			throw new Exception\Routing\RouteAlreadyExistsException("Can't add route '$mappedUrl', already exists.");

		$this->profiler()->createTimer('addRoute');

		// if route is callable, we just add it as it is
		if (is_callable($route))
		{
			$this->_urlMappings[$mappedUrl] = $mappedUrl;
			$this->_routes[$mappedUrl] = $route;
			return $this;
		}

		$route = (object)$route;

		foreach ($this->request()->httpMethodList() as $method)
		{
			if (isset($route->{$method}) && !is_callable($route->{$method}))
			{
				// replacing dots with backslashes
				$route->{$method} = str_replace('.', '\\', $route->{$method});
				// if first char is not \, we prepend it (we use absolute paths)
				if (strpos($route->{$method}, '\\') !== 0)
					$route->{$method} = '\\' . $route->{$method};
			}
		}

		$routeName = isset($route->as) ? $route->as : $mappedUrl;
		$this->_urlMappings[$mappedUrl] = $routeName;

		if (isset($route->sameAs) && isset($this->_routes[$route->sameAs]))
		{
			$this->_routes[$routeName] = $this->_routes[$route->sameAs];
			$this->profiler()->stopTimer('addRoute');
			return $this;
		}

		$this->_routes[$routeName] = $route;
		$this->profiler()->stopTimer('addRoute');

		return $this;
	}

	/**
	 * Adds multiple routes.
	 *
	 * @param array $routes
	 */
	public function addRoutes(array $routes)
	{
		$this->profiler()->createTimer('addRoutes');
		foreach ($routes as $url => $route)
			$this->addRoute($url, $route);
		$this->profiler()->stopTimer('addRoutes');
	}

	/**
	 * Gets Request instance.
	 *
	 * @return \Naga\Core\Request\Request
	 */
	protected function request()
	{
		return $this->component('request');
	}

	/**
	 * Gets current uri without base path.
	 *
	 * @return string
	 */
	protected function uri()
	{
		if (!$this->_basePath)
			return $this->request()->uri();

		$basePath = $this->_basePath{strlen($this->_basePath) - 1} == '/' ? $this->_basePath : "{$this->_basePath}/";

		return str_replace($basePath, '', $this->request()->uri());
	}
}