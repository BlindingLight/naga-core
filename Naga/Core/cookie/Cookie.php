<?php

namespace Naga\Core\Cookie;

use Naga\Core\nComponent;

/**
 * Helper class for managing cookies.
 *
 * @author BlindingLight<bloodredshade@gmail.com>
 * @package Naga\Core\Cookie
 */
class Cookie extends nComponent
{
	/**
	 * Sets a cookie. Expiration must be in seconds, NOT including time()!
	 *
	 * @param string $name cookie name
	 * @param string $value cookie value
	 * @param null|int $expiration cookie's expiration time in seconds from current timestamp (if null, it's like a session)
	 * @param string $path cookie path (if null, its '/')
	 * @param string $domain cookie domain
	 * @param bool $httpOnly if true, javascript can't see the cookie
	 * @param bool $secure should transfer only through https connection?
	 * @return bool
	 */
	public function set($name, $value, $expiration = null, $path = '/',
							   $domain = null, $httpOnly = false, $secure = false)
	{
		$path = !$path ? '/' : $path;
		$expiration = !$expiration ? null : time() + $expiration;
		return setcookie($name, $value, $expiration, $path, $domain, $secure, $httpOnly);
	}

	/**
	 * Sets a cookie for ~2 years.
	 *
	 * @param string $name cookie name
	 * @param string $value cookie value
	 * @param string $path cookie path (if null, its '/')
	 * @param string $domain cookie domain
	 * @param bool $httpOnly if true, javascript can't see the cookie
	 * @param bool $secure should transfer only through https connection?
	 * @return bool
	 */
	public function forever($name, $value, $path = '/', $domain = null, $httpOnly = false, $secure = false)
	{
		return $this->set($name, $value, time() + 63072000, $path, $domain, $httpOnly, $secure);
	}

	/**
	 * Gets a cookie. If doesn't exist, return null.
	 *
	 * @param string $name cookie name
	 * @return null|mixed
	 */
	public function get($name)
	{
		if (isset($_COOKIE[$name]))
			return $_COOKIE[$name];

		return null;
	}

	/**
	 * Deletes a cookie.
	 *
	 * @param string $name cookie name
	 * @param string $path
	 * @param null|string $domain
	 * @return bool
	 */
	public function delete($name, $path = '/', $domain = null)
	{
		return setcookie($name, '', 1, $path, $domain);
	}

	/**
	 * Tells whether the cookie exists or not.
	 *
	 * @param string $name cookie name
	 * @return bool
	 */
	public function exists($name)
	{
		return isset($_COOKIE[$name]);
	}
}