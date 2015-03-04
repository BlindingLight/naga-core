<?php

namespace Naga\Core\Localization;

use Naga\Core\Cache\iCache;
use Naga\Core\nComponent;
use Naga\Core\Exception;

/**
 * Base language class.
 *
 * @author BlindingLight<bloodredshade@gmail.com>
 * @package Naga\Core\Localization
 */
class Language extends nComponent
{
	/**
	 * @var int
	 */
	private $_id;
	/**
	 * @var string
	 */
	private $_isoCode;

	public function __construct($id, $isoCode, iCache $cache)
	{
		$this->_id = $id;
		$this->_isoCode = $isoCode;
		$this->registerComponent('cache', $cache);
	}

	/**
	 * Sets the language iso code.
	 *
	 * @param string $isoCode
	 * @return Language
	 * @throws \Naga\Core\Exception\LocalizationException
	 */
	public function setIsoCode($isoCode)
	{
		if ($this->_isoCode)
			throw new Exception\LocalizationException("Iso code is already set for language, can't change it.");

		$this->_isoCode = $isoCode;
		return $this;
	}

	/**
	 * Gets the language iso code.
	 *
	 * @return string
	 */
	public function isoCode()
	{
		return $this->_isoCode;
	}

	/**
	 * Sets the language id.
	 *
	 * @param int $id
	 * @return Language
	 * @throws \Naga\Core\Exception\LocalizationException
	 */
	public function setId($id)
	{
		if ($this->_id)
			throw new Exception\LocalizationException("Id is already set for language, can't change it.");

		$this->_id = $id;
		return $this;
	}

	/**
	 * Gets the language id.
	 *
	 * @return int
	 */
	public function id()
	{
		return $this->_id;
	}

	/**
	 * Gets a translated string.
	 *
	 * @param string $name
	 * @param null|mixed $default
	 * @return mixed|null
	 */
	public function get($name, $default = null)
	{
		$rname = $this->isoCode() . $name;
		return $this->cache()->get($rname, !is_null($default) ? $default : $name);
	}

	/**
	 * Stores a translated string in the cache.
	 *
	 * @param string $name
	 * @param mixed $value
	 * @return bool
	 */
	public function set($name, $value)
	{
		$name = $this->isoCode() . $name;
		// TODO: proper ttl for language item cache storing
		return $this->cache()->set($name, $value, 0);
	}

	/**
	 * Gets the cache instance.
	 *
	 * @return iCache
	 */
	protected function cache()
	{
		return $this->component('cache');
	}
}