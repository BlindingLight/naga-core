<?php

namespace Naga\Core\Debug\Log;

use Naga\Core\nComponent;

/**
 * Class for logging to javascript console.
 *
 * @package Naga\Core\Debug\Log
 * @author  BlindingLight<bloodredshade@gmail.com>
 */
class JsConsoleLogger extends nComponent implements iLogger
{
	/**
	 * @var array log groups and messages
	 */
	protected $_messages = array();

	/**
	 * @var string current log group
	 */
	protected $_currentGroup;

	/**
	 * @var string logger name
	 */
	private $_name;

	public function __construct($name = 'application')
	{
		$this->_name = $name;
		$this->_messages[$name] = array();
		$this->_currentGroup = $name;
	}

	/**
	 * @see iLogger
	 */
	public function generate()
	{
		if (!count($this->_messages))
			return $this;

		ksort($this->_messages);

		$previousGroupName = $this->_name;
		$generated = '';
		foreach ($this->_messages as $group => $messages)
		{
			if (!count($messages))
				continue;

			$groupName = $group;
			if (strpos($group, $previousGroupName . '.') === 0)
				$groupName = str_replace($previousGroupName . '.', '', $group);
			else if ($generated)
				$generated .= 'console.groupEnd();';

			$generated .= "console.groupCollapsed('{$groupName}');";

			foreach ($messages as $message)
			{
				$func = $this->getJsSeverityFunctionName($message->severity);
				$color = $this->getJsSeverityColor($message->severity);
				$message->message = str_replace("\n", '\n', $message->message);
				$generated .= "console.{$func}('%c{$message->message}', 'color: #{$color}');";
			}

			$generated .= 'console.groupEnd();';
		}

		if ($generated)
			$generated = '<script type="text/javascript">' . $generated . '</script>';

		return $generated;
	}

	/**
	 * Gets javascript console function name of specified severity.
	 *
	 * @param int $severity
	 * @return string
	 */
	protected function getJsSeverityFunctionName($severity)
	{
		switch ($severity)
		{
			case iLogger::Info:
				return 'log';
			case iLogger::Error:
				return 'error';
			case iLogger::Alert:
				return 'error';
			case iLogger::Critical:
				return 'error';
			case iLogger::Emergency:
				return 'error';
			case iLogger::Warning:
				return 'warn';
			case iLogger::Debug:
				return 'debug';
			case iLogger::Notice:
				return 'info';
			default:
				return 'log';
		}
	}

	/**
	 * Gets javascript console color of specified severity.
	 *
	 * @param int $severity
	 * @return string
	 */
	protected function getJsSeverityColor($severity)
	{
		switch ($severity)
		{
			case iLogger::Info:
				return '607D8B';
			case iLogger::Error:
				return 'C62828';
			case iLogger::Alert:
				return 'E91E63';
			case iLogger::Critical:
				return 'B71C1C';
			case iLogger::Emergency:
				return '880E4F';
			case iLogger::Warning:
				return 'FB8C00';
			case iLogger::Debug:
				return '03A9F4';
			case iLogger::Notice:
				return '78909C';
			default:
				return '607D8B';
		}
	}

	/**
	 * @see iLogger
	 */
	public function dispatch()
	{
		echo $this->generate();
	}

	/**
	 * @see iLogger
	 */
	public function group($name)
	{
		if (!isset($this->_messages[$name]))
			$this->_messages[$name] = array();

		$this->_currentGroup = $name;

		return $this;
	}

	/**
	 * @see iLogger
	 */
	public function log($severity, $message)
	{
		$logArgs = func_get_args();
		$this->_messages[$this->_currentGroup][] = (object)array(
			'severity' => (int)$severity,
			'message' => call_user_func_array('sprintf', array_merge(array($message), array_splice($logArgs, 2)))
		);

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
		$this->_messages = array(
			$this->_name => array()
		);

		return $this;
	}
}