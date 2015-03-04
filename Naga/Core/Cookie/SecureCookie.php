<?php

namespace Naga\Core\Cookie;

/**
 * Helper class for managing secure cookies (hmac).
 *
 * @package Naga\Core\Cookie
 */
class SecureCookie extends Cookie
{
	/**
	 * @var string HMAC secret key
	 */
	private $_secretKey;

	/**
	 * Sets the HMAC secret key.
	 *
	 * @param string $key
	 */
	public function setSecretKey($key)
	{
		$this->_secretKey = $key;
	}

	/**
	 * Sets a secure cookie. Expiration must be in seconds, NOT including time()!
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
		parent::set(
			$name,
			$value . '|' . $expiration . '|' . $this->generateHMAC($value, $expiration),
			$expiration,
			$path,
			$domain,
			true
		);
	}

	/**
	 * Sets a secure cookie for ~2 years.
	 *
	 * @param string $name cookie name
	 * @param string $value cookie value
	 * @param string $path cookie path (if null, its '/')
	 * @param string $domain cookie domain
	 * @param bool $httpOnly if true, javascript can't see the cookie
	 * @param bool $secure should transfer only through https connection?
	 * @return bool
	 */
	public function forever($name, $value, $path = '/',
							   $domain = null, $httpOnly = false, $secure = false)
	{
		$expiration = time() + 63072000;
		parent::set(
			$name,
			$value . '|' . $expiration . '|' . $this->generateHMAC($value, $expiration),
			$expiration,
			$path,
			$domain,
			true
		);
	}

	/**
	 * Gets secure cookie. Returns null if the cookie doesn't exist.
	 *
	 * @param string $name cookie name
	 * @return null|mixed
	 */
	public function get($name)
	{
		if (!$this->exists($name) || is_null(parent::get($name)) || !$this->verify(parent::get($name)))
			return null;

		$data = explode('|', parent::get($name));
		return $data[0];
	}

	/**
	 * Verifies a secure cookie.
	 *
	 * @param string $cookieData cookie data
	 * @return bool
	 */
	public function verify($cookieData)
	{
		list($data, $expiration, $hmac) = explode('|', $cookieData);
		if ($expiration != 0 && $expiration < time())
			return false;

		$hash = $this->generateHMAC($data, $expiration);
		return $hmac == $hash;
	}

	/**
	 * Generates a HMAC hash.
	 *
	 * @param string $data cookie data
	 * @param string $expiration cookie expiration
	 * @return string
	 */
	private function generateHMAC($data, $expiration)
	{
		$key = hash_hmac('md5', $data . $expiration, $this->_secretKey);
		$hash = hash_hmac('md5', $data . $expiration, $key);

		return $hash;
	}
}