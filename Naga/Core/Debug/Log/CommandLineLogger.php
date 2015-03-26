<?php

namespace Naga\Core\Debug\Log;

use Naga\Core\nComponent;

class CommandLineLogger extends nComponent implements iLogger
{
	protected $_messages = array();

	/**
	 * @see iLogger
	 */
	public function generate()
	{
		return '';
	}

	/**
	 * @see iLogger
	 */
	public function dispatch()
	{
	}

	/**
	 * @see iLogger
	 */
	public function group($name)
	{
		return $this;
	}

	/**
	 * @see iLogger
	 */
	public function log($severity, $message)
	{
		$logArgs = func_get_args();
		$message = (object)array(
			'severity' => (int)$severity,
			'message' => call_user_func_array('sprintf', array_merge(array($message), array_splice($logArgs, 2)))
		);
		$this->_messages[] = $message;

		echo $message->message . "\n";

		return $this;
	}

	/**
	 * @see iLogger
	 */
	public function debug($message)
	{
		$logArgs = func_get_args();
		array_shift($logArgs);
		return call_user_func_array(array($this, 'log'), array_merge(array(iLogger::Debug, $message), $logArgs));
	}

	/**
	 * @see iLogger
	 */
	public function info($message)
	{
		$logArgs = func_get_args();
		array_shift($logArgs);
		return call_user_func_array(array($this, 'log'), array_merge(array(iLogger::Info, $message), $logArgs));
	}

	/**
	 * @see iLogger
	 */
	public function notice($message)
	{
		$logArgs = func_get_args();
		array_shift($logArgs);
		return call_user_func_array(array($this, 'log'), array_merge(array(iLogger::Notice, $message), $logArgs));
	}

	/**
	 * @see iLogger
	 */
	public function warning($message)
	{
		$logArgs = func_get_args();
		array_shift($logArgs);
		return call_user_func_array(array($this, 'log'), array_merge(array(iLogger::Warning, $message), $logArgs));
	}

	/**
	 * @see iLogger
	 */
	public function error($message)
	{
		$logArgs = func_get_args();
		array_shift($logArgs);
		return call_user_func_array(array($this, 'log'), array_merge(array(iLogger::Error, $message), $logArgs));
	}

	/**
	 * @see iLogger
	 */
	public function critical($message)
	{
		$logArgs = func_get_args();
		array_shift($logArgs);
		return call_user_func_array(array($this, 'log'), array_merge(array(iLogger::Critical, $message), $logArgs));
	}

	/**
	 * @see iLogger
	 */
	public function alert($message)
	{
		$logArgs = func_get_args();
		array_shift($logArgs);
		return call_user_func_array(array($this, 'log'), array_merge(array(iLogger::Alert, $message), $logArgs));
	}

	/**
	 * @see iLogger
	 */
	public function emergency($message)
	{
		$logArgs = func_get_args();
		array_shift($logArgs);
		return call_user_func_array(array($this, 'log'), array_merge(array(iLogger::Emergency, $message), $logArgs));
	}

	/**
	 * @see iLogger
	 */
	public function reset()
	{
		$this->_messages = array();

		return $this;
	}
}