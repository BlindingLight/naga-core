<?php

namespace Naga\Core\Routing;

use Naga\Core\Request\Request;
use Naga\Core\nComponent;

/**
 * Class for generating urls.
 *
 * @author BlindingLight<bloodredshade@gmail.com>
 * @package Naga\Core\Routing
 */
class UrlGenerator extends nComponent
{
	/**
	 * @var array
	 */
	private $_routes = array();
	/**
	 * @var string
	 */
	private $_resourceRoot = '/';

	/**
	 * Construct.
	 *
	 * @param array $routes
	 * @param Request $request current request
	 * @param string $resourceRoot
	 */
	public function __construct(array $routes, Request $request, $resourceRoot = '/')
	{
		foreach ($routes as $mappedUrl => $route)
		{
			if (is_callable($route))
			{
				$this->addRoute($mappedUrl, $mappedUrl);
				continue;
			}

			if (is_array($route))
				$route = (object)$route;

			if (is_object($route) && isset($route->as))
			{
				if (isset($route->sameAs) && isset($this->_routes[$route->sameAs]))
					$this->addRoute($route->as, $this->_routes[$route->sameAs]);
				else
					$this->addRoute($route->as, $mappedUrl);
			}
			else
				$this->addRoute($mappedUrl, $mappedUrl);
		}

		$this->registerComponent('request', $request);
		$this->_resourceRoot = $resourceRoot;
	}

	/**
	 * Adds a route. If $mappedUrl is empty, $name will be used as $mappedUrl.
	 *
	 * @param string $name
	 * @param string $mappedUrl
	 */
	public function addRoute($name, $mappedUrl = null)
	{
		$this->_routes[$name] = $mappedUrl ? $mappedUrl : $name;
	}

	/**
	 * Removes a route.
	 *
	 * @param string $name
	 */
	public function removeRoute($name)
	{
		if (isset($this->_routes[$name]))
			unset($this->_routes[$name]);
	}

	/**
	 * Gets properties from string. Format: name:value|name2:value2
	 *
	 * @param string $properties
	 * @return array
	 */
	public function getPropertiesFromString($properties = '')
	{
		// id:test|name:nobody
		$tmp = explode('|', $properties);
		$prepared = array();
		foreach ($tmp as $property)
		{
			$ptemp = explode(':', $property);
			if (count($ptemp))
				$prepared[$ptemp[0]] = isset($ptemp[1]) ? $ptemp[1] : '';
		}

		return $prepared;
	}

	/**
	 * Generates url from the specified mapped url. $properties can be an array or a string. If it's an array,
	 * it should be array('placeholderName' => 'value', ...). If string, it should be
	 * 'placeholderName:value|placeholder2Name:value@domain@secure'. @domain and @secure are optional. String $properties
	 * is about 2x slower than array $properties.
	 * Example:
	 * mappedUrl('user/{id|[0-9]+}', array('id' => 1), true, true) -> https://domain.com/user/1
	 * mappedUrl('user/{id|[0-9]+}', 'id:1@domain@secure') -> https://domain.com/user/1
	 *
	 * @param string $mappedUrl
	 * @param string $properties
	 * @param bool $withDomain
	 * @param bool $secure
	 * @return string
	 */
	public function mappedUrl($mappedUrl, $properties = '', $withDomain = false, $secure = false)
	{
		return $this->route($mappedUrl, $properties, $withDomain, $secure);
	}


	/**
	 * Generates url from the specified route's mapped url. If route doesn't exist, it uses $name as $mappedUrl.
	 * $properties can be an array or a string. If it's an array, it should be
	 * array('placeholderName' => 'value', ...). If string, it should be
	 * 'placeholderName:value|placeholder2Name:value@domain@secure'. @domain and @secure are optional. String $properties
	 * is about 2x slower than array $properties.
	 * Example:
	 * mappedUrl('user/{id|[0-9]+}', array('id' => 1), true, true) -> https://domain.com/user/1
	 * mappedUrl('user/{id|[0-9]+}', 'id:1@domain@secure') -> https://domain.com/user/1
	 *
	 * @param string $name
	 * @param string $properties
	 * @param bool $withDomain
	 * @param bool $secure
	 * @return string
	 */
	public function route($name, $properties = '', $withDomain = false, $secure = false)
	{
		$preparedUrl = isset($this->_routes[$name]) ? $this->prepareRouteMappedUrl($this->_routes[$name]) : $name;

		if (is_string($properties))
		{
			if (strpos($properties, '@domain') !== false)
			{
				$withDomain = true;
				$properties = str_replace('@domain', '', $properties);
			}
			if (strpos($properties, '@secure') !== false)
			{
				$secure = true;
				$properties = str_replace('@secure', '', $properties);
			}
		}
		$properties = is_array($properties) ? $properties : $this->getPropertiesFromString($properties);

		foreach ($properties as $name => $value)
		$preparedUrl = str_replace('{' . $name . '}', $value, $preparedUrl);

		return ($withDomain ? ($secure ? 'https://' : 'http://') . $this->request()->domainName() . '/' : '/') . $preparedUrl;
	}

	/**
	 * Prepares a mapped url. (changes {name|regexp} to {name})
	 *
	 * @param $mappedUrl
	 * @return mixed
	 */
	public function prepareRouteMappedUrl($mappedUrl)
	{
		return preg_replace('#\{(\w+)\|[^\}]+}#', '{$1}', $mappedUrl);
	}

	/**
	 * Gets a resource url.
	 *
	 * @param string $path
	 * @return string
	 */
	public function resource($path)
	{
		return $this->resourceRoot() . $path;
	}

	/**
	 * Converts string to url.
	 *
	 * @param string $string
	 * @return string
	 */
	public function urlify($string)
	{
		return trim(
			preg_replace(
				'/-+/',
				'-',
				str_replace(
					array(' ', ',', ':', '?', '.', '/', '\'', '"', '\\'),
					'-',
					strtolower($this->replaceAccents($string))
				)
			),
			'-'
		);
	}

	/**
	 * Converts accented characters to non-accented.
	 *
	 * @param string $str
	 * @return string
	 */
	protected function replaceAccents($str)
	{
		$a = array('À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Æ', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ð', 'Ñ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ø', 'Ù', 'Ú', 'Û', 'Ü', 'Ý', 'ß', 'à', 'á', 'â', 'ã', 'ä', 'å', 'æ', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ñ', 'ò', 'ó', 'ô', 'õ', 'ö', 'ø', 'ù', 'ú', 'û', 'ü', 'ý', 'ÿ', 'Ā', 'ā', 'Ă', 'ă', 'Ą', 'ą', 'Ć', 'ć', 'Ĉ', 'ĉ', 'Ċ', 'ċ', 'Č', 'č', 'Ď', 'ď', 'Đ', 'đ', 'Ē', 'ē', 'Ĕ', 'ĕ', 'Ė', 'ė', 'Ę', 'ę', 'Ě', 'ě', 'Ĝ', 'ĝ', 'Ğ', 'ğ', 'Ġ', 'ġ', 'Ģ', 'ģ', 'Ĥ', 'ĥ', 'Ħ', 'ħ', 'Ĩ', 'ĩ', 'Ī', 'ī', 'Ĭ', 'ĭ', 'Į', 'į', 'İ', 'ı', 'Ĳ', 'ĳ', 'Ĵ', 'ĵ', 'Ķ', 'ķ', 'Ĺ', 'ĺ', 'Ļ', 'ļ', 'Ľ', 'ľ', 'Ŀ', 'ŀ', 'Ł', 'ł', 'Ń', 'ń', 'Ņ', 'ņ', 'Ň', 'ň', 'ŉ', 'Ō', 'ō', 'Ŏ', 'ŏ', 'Ő', 'ő', 'Œ', 'œ', 'Ŕ', 'ŕ', 'Ŗ', 'ŗ', 'Ř', 'ř', 'Ś', 'ś', 'Ŝ', 'ŝ', 'Ş', 'ş', 'Š', 'š', 'Ţ', 'ţ', 'Ť', 'ť', 'Ŧ', 'ŧ', 'Ũ', 'ũ', 'Ū', 'ū', 'Ŭ', 'ŭ', 'Ů', 'ů', 'Ű', 'ű', 'Ų', 'ų', 'Ŵ', 'ŵ', 'Ŷ', 'ŷ', 'Ÿ', 'Ź', 'ź', 'Ż', 'ż', 'Ž', 'ž', 'ſ', 'ƒ', 'Ơ', 'ơ', 'Ư', 'ư', 'Ǎ', 'ǎ', 'Ǐ', 'ǐ', 'Ǒ', 'ǒ', 'Ǔ', 'ǔ', 'Ǖ', 'ǖ', 'Ǘ', 'ǘ', 'Ǚ', 'ǚ', 'Ǜ', 'ǜ', 'Ǻ', 'ǻ', 'Ǽ', 'ǽ', 'Ǿ', 'ǿ');
		$b = array('A', 'A', 'A', 'A', 'A', 'A', 'AE', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'D', 'N', 'O', 'O', 'O', 'O', 'O', 'O', 'U', 'U', 'U', 'U', 'Y', 'ss', 'a', 'a', 'a', 'a', 'a', 'a', 'ae', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'n', 'o', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'y', 'y', 'A', 'a', 'A', 'a', 'A', 'a', 'C', 'c', 'C', 'c', 'C', 'c', 'C', 'c', 'D', 'd', 'D', 'd', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'G', 'g', 'G', 'g', 'G', 'g', 'G', 'g', 'H', 'h', 'H', 'h', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'IJ', 'ij', 'J', 'j', 'K', 'k', 'L', 'l', 'L', 'l', 'L', 'l', 'L', 'l', 'l', 'l', 'N', 'n', 'N', 'n', 'N', 'n', 'n', 'O', 'o', 'O', 'o', 'O', 'o', 'OE', 'oe', 'R', 'r', 'R', 'r', 'R', 'r', 'S', 's', 'S', 's', 'S', 's', 'S', 's', 'T', 't', 'T', 't', 'T', 't', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'W', 'w', 'Y', 'y', 'Y', 'Z', 'z', 'Z', 'z', 'Z', 'z', 's', 'f', 'O', 'o', 'U', 'u', 'A', 'a', 'I', 'i', 'O', 'o', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'A', 'a', 'AE', 'ae', 'O', 'o');
		return str_replace($a, $b, $str);
	}

	/**
	 * Gets the Request instance.
	 *
	 * @return Request
	 */
	protected function request()
	{
		return $this->component('request');
	}

	/**
	 * Gets the resource root.
	 *
	 * @return string
	 */
	protected function resourceRoot()
	{
		return $this->_resourceRoot;
	}
}