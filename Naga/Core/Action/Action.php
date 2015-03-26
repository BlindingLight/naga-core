<?php

namespace Naga\Core\Action;

use Naga\Core\View\View;
use Naga\Core\nComponent;

/**
 * Base action class. You can use action classes for business logic.
 *
 * @author BlindingLight<bloodredshade@gmail.com>
 * @package Naga\Core\Action
 */
abstract class Action extends nComponent
{
	/**
	 * @var array action parameters from controller
	 */
	private $_params = array();
	/**
	 * @var View View instance
	 */
	private $_view;

	/**
	 * Construct.
	 *
	 * @param array $params
	 * @param View $view
	 */
	public function __construct(array $params = array(), View $view = null)
	{
		$this->_params = $params;
		if ($view)
			$this->_view = $view;
	}

	/**
	 * Gets a parameter.
	 *
	 * @param $name
	 * @return null|mixed
	 */
	public function get($name)
	{
		return isset($this->_params[$name]) ? $this->_params[$name] : null;
	}

	/**
	 * Sets a parameter.
	 *
	 * @param string $name
	 * @param mixed $value
	 */
	public function set($name, $value)
	{
		$this->_params[$name] = $value;
	}

	/**
	 * Gets all parameters.
	 *
	 * @return array
	 */
	public function params()
	{
		return $this->_params;
	}

	/**
	 * Sets the View instance.
	 *
	 * @param View $view
	 */
	public function setView(View $view)
	{
		$this->_view = $view;
	}

	/**
	 * Gets the view instance.
	 *
	 * @return View
	 */
	public function view()
	{
		return $this->_view;
	}

	/**
	 * Gets root part of current uri.
	 *
	 * @return string
	 */
	protected function rootUriPart()
	{
		return $this->get('rootUriPart');
	}

	abstract public function execute();
}