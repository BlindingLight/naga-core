<?php

namespace Naga\Core\Request;

use Naga\Core\nComponent;
use Naga\Core\Exception;

/**
 * A class that represents a request.
 *
 * @author BlindingLight<bloodredshade@gmail.com>
 * @package Naga\Core\Request
 */
class Request extends nComponent
{
	const MethodGet = 1;
	const MethodPost = 2;
	const MethodPut = 3;
	const MethodDelete = 4;
	const MethodOptions = 5;
	const MethodHead = 6;
	const MethodTrace = 7;
	const MethodConnect = 8;

	/**
	 * @var int
	 */
	private $_httpMethod;
	/**
	 * @var string
	 */
	private $_httpMethodString;
	/**
	 * @var array
	 */
	private $_headers;
	/**
	 * @var string request content
	 */
	private $_content;

	/**
	 * Construct. It prepares headers.
	 */
	public function __construct()
	{
		$this->_headers = function_exists('getallheaders') ? getallheaders() : array();
		if (!count($this->_headers))
		{
			foreach($_SERVER as $key => $value)
			{
				if (strpos($key, 'HTTP_') === 0)
					$this->_headers[str_replace(' ', '-', ucwords(str_replace('_', ' ', strtolower(substr($key, 5)))))] = $value;
			}
		}
	}

	/**
	 * Gets the query string of the request.
	 *
	 * @return string
	 */
	public function queryString()
	{
		return substr($this->fullUri(), strpos($this->fullUri(), '?'), strlen($this->fullUri()) - 1);
	}

	/**
	 * Gets the http authenticated user name.
	 *
	 * @return string|null
	 */
	public function httpUser()
	{
		return $this->server('PHP_AUTH_USER');
	}

	/**
	 * Gets the http authenticated user password.
	 *
	 * @return string|null
	 */
	public function httpPassword()
	{
		return $this->server('PHP_AUTH_PW');
	}

	/**
	 * Gets the port on which the request was made.
	 *
	 * @return int
	 */
	public function port()
	{
		return $this->header('X-Forwarded-Port') ? (int)$this->header('X-Forwarded-Port') : (int)$this->server('SERVER_PORT');
	}

	/**
	 * Gets the request as a string.
	 *
	 * @return string
	 */
	public function __toString()
	{
		return $this->requestAsString();
	}

	/**
	 * Gets the request as a string.
	 *
	 * @return string
	 */
	public function requestAsString()
	{
		$headers = '';
		foreach ($this->headers() as $name => $value)
			$headers .= $name . ': ' . $value . "\r\n";

		return sprintf('%s %s %s', strtoupper($this->httpMethodString()), $this->uri(), $this->server('SERVER_PROTOCOL'))."\r\n".
				$headers."\r\n".
				$this->content();
	}

	/**
	 * Gets the client's ip.
	 *
	 * @return string
	 * @throws \Naga\Core\Exception\RequestException
	 */
	public function clientIp()
	{
		$keys = array(
			'HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP',
			'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR'
		);
		$keys = array_values(array_intersect($keys, array_keys($_SERVER)));
		foreach ($keys as $key)
		{
			foreach (explode(',', $this->server($key)) as $ip)
			{
				$ip = trim($ip);
				if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE) !== false)
					return $ip;
			}
		}

		throw new Exception\RequestException("Can't get client ip.");
	}

	/**
	 * Gets an item from the $_SERVER variable.
	 *
	 * @param string $name
	 * @return null|mixed
	 */
	public function server($name)
	{
		return isset($_SERVER[$name]) ? $_SERVER[$name] : null;
	}

	/**
	 * Gets a request header. Example: $input->get('Accept-Encoding'), $input-get('Cache-Control')
	 *
	 * @param string $name
	 * @return null|string
	 */
	public function header($name)
	{
		return isset($this->_headers[$name]) ? $this->_headers[$name] : null;
	}

	/**
	 * Gets all request headers.
	 *
	 * @return array
	 */
	public function headers()
	{
		return $this->_headers;
	}

	/**
	 * Gets the request content.
	 *
	 * @return string
	 */
	public function content()
	{
		if ($this->_content === null)
			$this->_content = file_get_contents('php://input');

		return $this->_content;
	}

	/**
	 * Returns whether a request is an ajax request.
	 *
	 * @return bool
	 */
	public function isAjax()
	{
		return $this->header('X-Requested-With') == 'XMLHttpRequest';
	}

	/**
	 * Returns whether a request is a POST request.
	 *
	 * @return bool
	 */
	public function isPost()
	{
		return $this->httpMethod() == self::MethodPost;
	}

	/**
	 * Returns whether a request is a GET request.
	 *
	 * @return bool
	 */
	public function isGet()
	{
		return $this->httpMethod() == self::MethodGet;
	}

	/**
	 * Returns whether a request is a PUT request.
	 *
	 * @return bool
	 */
	public function isPut()
	{
		return $this->httpMethod() == self::MethodPut;
	}

	/**
	 * Returns whether a request is a DELETE request.
	 *
	 * @return bool
	 */
	public function isDelete()
	{
		return $this->httpMethod() == self::MethodDelete;
	}

	/**
	 * Returns whether a request is a CONNECT request.
	 *
	 * @return bool
	 */
	public function isConnect()
	{
		return $this->httpMethod() == self::MethodConnect;
	}

	/**
	 * Returns whether a request is a TRACE request.
	 *
	 * @return bool
	 */
	public function isTrace()
	{
		return $this->httpMethod() == self::MethodTrace;
	}

	/**
	 * Returns whether a request is an OPTIONS request.
	 *
	 * @return bool
	 */
	public function isOptions()
	{
		return $this->httpMethod() == self::MethodOptions;
	}

	/**
	 * Returns whether a request is a HEAD request.
	 *
	 * @return bool
	 */
	public function isHead()
	{
		return $this->httpMethod() == self::MethodHead;
	}

	/**
	 * Gets the request uri without query string, example: if url is domain.com/home?a=1&b=2, returns /home.
	 *
	 * @return string
	 */
	public function uri()
	{
		$uri = $this->fullUri();
		$queryPos = strpos($uri, '?');
		$uri = substr($uri, 0, $queryPos !== false ? $queryPos : strlen($uri));
		return $uri == '/' ? '' : $uri;
	}

	/**
	 * Gets the request uri with query string, example: if url is domain.com/home?a=1&b=2, returns /home?a=1&b=2.
	 *
	 * @return string
	 */
	public function fullUri()
	{
		return $_SERVER['REQUEST_URI'] ? $_SERVER['REQUEST_URI'] : '/';
	}

	/**
	 * Gets an uri segment with the specified index.
	 *
	 * @param int $index
	 * @return null|string
	 */
	public function uriSegment($index)
	{
		$uriParts = explode('/', $this->uri());
		return isset($uriParts[$index]) ? $uriParts[$index] : null;
	}

	/**
	 * Gets the current url.
	 *
	 * @return string
	 */
	public function url()
	{
		return ($this->secure() ? 'https' : 'http') . '://' . $this->domainName() . $this->uri();
	}

	/**
	 * Returns whether the request uses SSL connection. (https)
	 *
	 * @return bool
	 */
	public function secure()
	{
		return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off')
			|| isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && ($_SERVER['HTTP_X_FORWARDED_PROTO']
				&& $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https');
	}

	/**
	 * Gets the requested domain name.
	 *
	 * @return string
	 */
	public function domainName()
	{
		return isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'];
	}

	/**
	 * Gets the requested host name.
	 *
	 * @return mixed
	 */
	public function host()
	{
		return isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : $_SERVER['SERVER_ADDR'];
	}

	/**
	 * Gets the http method as int (see class constants).
	 *
	 * @return int
	 */
	public function httpMethod()
	{
		if (!$this->_httpMethodString)
		{
			$this->_httpMethodString = strtolower($_SERVER['REQUEST_METHOD']);
			$this->_httpMethod = $this->getHttpMethodFromString($this->_httpMethodString);
		}

		return $this->_httpMethod;
	}

	/**
	 * Gets the http method as lowercase string.
	 *
	 * @return string
	 */
	public function httpMethodString()
	{
		$this->httpMethod();
		return $this->_httpMethodString;
	}

	/**
	 * Sets the http method. Usable if you want to alter the request. See class constants for possible values.
	 *
	 * @param int $method
	 * @throws \Naga\Core\Exception\ArgumentMismatchException
	 */
	public function setHttpMethod($method)
	{
		if (!is_numeric($method) || $method < 1 || $method > 8)
			throw new Exception\ArgumentMismatchException("You should set http method with Request class constants.");
		
		$this->_httpMethod = $method;
	}

	/**
	 * Gets the http method as int from a http method string. See class constants for possible values.
	 *
	 * @param string $methodString
	 * @return int
	 * @throws \Naga\Core\Exception\ArgumentMismatchException
	 */
	public function getHttpMethodFromString($methodString)
	{
		$methodString = strtolower($methodString);
		if ($methodString == 'get')
			return self::MethodGet;
		else if ($methodString == 'post')
			return self::MethodPost;
		else if ($methodString == 'put')
			return self::MethodPut;
		else if ($methodString == 'delete')
			return self::MethodDelete;
		else if ($methodString == 'options')
			return self::MethodOptions;
		else if ($methodString == 'head')
			return self::MethodHead;
		else if ($methodString == 'trace')
			return self::MethodTrace;
		else if ($methodString == 'connect')
			return self::MethodConnect;
		
		throw new Exception\ArgumentMismatchException("Invalid http method string specified.");
	}

	/**
	 * Gets the http method as lowercase string from the specified http method. See class constants for possible $method
	 * values.
	 *
	 * @param int $method
	 * @return string
	 * @throws \Naga\Core\Exception\ArgumentMismatchException
	 */
	public function getStringFromHttpMethod($method)
	{
		if ($method == self::MethodGet)
			return 'get';
		else if ($method == self::MethodPost)
			return 'post';
		else if ($method == self::MethodPut)
			return 'put';
		else if ($method == self::MethodDelete)
			return 'delete';
		else if ($method == self::MethodOptions)
			return 'options';
		else if ($method == self::MethodHead)
			return 'head';
		else if ($method == self::MethodTrace)
			return 'trace';
		else if ($method == self::MethodConnect)
			return 'connect';
		
		throw new Exception\ArgumentMismatchException("Invalid http method specified.");
	}

	/**
	 * Gets an array with the http methods in format array(constValue => string)
	 *
	 * @return array
	 */
	public function httpMethodList()
	{
		return array(
			self::MethodGet => 'get',
			self::MethodPost => 'post',
			self::MethodPut => 'put',
			self::MethodDelete => 'delete',
			self::MethodOptions => 'options',
			self::MethodHead => 'head',
			self::MethodTrace => 'trace',
			self::MethodConnect => 'connect'
		);
	}
}