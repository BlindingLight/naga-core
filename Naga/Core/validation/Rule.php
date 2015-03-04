<?php

namespace Naga\Core\Validation;

use Naga\Core\nComponent;

class Rule extends nComponent
{
	/**
	 * @var callable rule callback
	 */
	private $_callback;

	/**
	 * @var string error message
	 */
	private $_message;

	/**
	 * Construct.
	 *
	 * @param callable $callback rule callback
	 * @param string $message error message
	 */
	public function __construct(Callable $callback, $message = '')
	{
		$this->_callback = $callback;
		$this->_message = $message;
	}

	/**
	 * If any properties called as a method, we call $_callback.
	 *
	 * @param string $method not used
	 * @param array $args callback arguments
	 * @return bool
	 */
	public function __call($method, $args)
	{
		$method = '_callback';
		return call_user_func_array($this->$method, $args);
	}

	/**
	 * Changes the Rule's error message.
	 *
	 * @param string $message
	 */
	public function changeMessage($message)
	{
		$this->_message = $message;
	}

	/**
	 * Gets the Rule's error message.
	 *
	 * @return string
	 */
	public function message()
	{
		return $this->_message;
	}
}