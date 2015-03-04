<?php

namespace Naga\Core\Localization;

use Naga\Core\Exception;
use Naga\Core\nComponent;

/**
 * Basic localization component.
 *
 * @author BlindingLight<bloodredshade@gmail.com>
 * @package Naga\Core\Localization
 */
class Localization extends nComponent
{
	private $_languages = array();
	private $_currentLanguageId;
	private $_defaultLanguageId;

	public function __construct()
	{
	}

	public function autodetectLanguage($acceptLanguageHeader)
	{

	}

	/**
	 * Adds a Language instance.
	 *
	 * @param $id
	 * @param Language $language
	 */
	public function addLanguage($id, Language $language)
	{
		$this->_languages[$id] = $language;
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
		return $this->currentLanguage()->get($name, $default);
	}

	/**
	 * Sets the current language.
	 *
	 * @param int $languageId
	 * @throws \Naga\Core\Exception\LocalizationException
	 */
	public function setCurrentLanguage($languageId)
	{
		if (!isset($this->_languages[$languageId]))
			throw new Exception\LocalizationException("Can't set current language to {$languageId}, doesn't exist.");

		$this->_currentLanguageId = $languageId;
	}

	/**
	 * Sets the default language.
	 *
	 * @param int $languageId
	 * @throws \Naga\Core\Exception\LocalizationException
	 */
	public function setDefaultLanguage($languageId)
	{
		if (!isset($this->_languages[$languageId]))
			throw new Exception\LocalizationException("Can't set default language to {$languageId}, doesn't exist.");

		$this->_defaultLanguageId = $languageId;
		if (!$this->_currentLanguageId)
			$this->_currentLanguageId = $languageId;
	}

	/**
	 * Gets the current language instance.
	 *
	 * @return Language
	 * @throws \Naga\Core\Exception\LocalizationException
	 */
	public function currentLanguage()
	{
		if (!isset($this->_languages[$this->_currentLanguageId]))
			throw new Exception\LocalizationException("Can't get current language with id {$this->_currentLanguageId}, doesn't exist.");

		return $this->_languages[$this->_currentLanguageId];
	}

	/**
	 * Gets the language with the specified id.
	 *
	 * @param int $languageId
	 * @return Language
	 * @throws \Naga\Core\Exception\LocalizationException
	 */
	public function language($languageId)
	{
		if (!isset($this->_languages[$languageId]))
			throw new Exception\LocalizationException("Can't get language with id {$languageId}, doesn't exist.");

		return $this->_languages[$languageId];
	}

	/**
	 * Gets all languages.
	 *
	 * @return array
	 */
	public function languages()
	{
		return $this->_languages;
	}
}