<?php

namespace Naga\Core\Validation;

use Naga\Core\nComponent;

class ValidationResult extends nComponent
{
	/**
	 * @var array data statuses
	 */
	private $_dataStatuses = array();

	/**
	 * Construct.
	 */
	public function __construct()
	{

	}

	/**
	 * Gets if a data with key $dataKey is valid. If no $dataKey specified, checks all data, and if any of them is invalid,
	 * return false.
	 *
	 * @param null|string $dataKey
	 * @return bool
	 */
	public function valid($dataKey = null)
	{
		// if no $dataKey specified, we validate all data
		if (!$dataKey)
		{
			foreach ($this->_dataStatuses as $status)
			{
				// if any of them is invalid, we return false
				if (!$status->valid)
					return false;
			}

			// everything is okay
			return true;
		}

		// if $dataKey specified, we check that we have status for it
		if (!isset($this->_dataStatuses[$dataKey]))
			return false;

		// return the status we found
		return $this->_dataStatuses[$dataKey]->valid;
	}

	/**
	 * Gets messages for data with key $dataKey. If no $dataKey specified, gets all messages. You can specify
	 * displayable names in $displayableNames for data that can be used in messages with placeholder :displayableName.
	 *
	 * Examples (let's assume we used :displayableName in Rule messages):
	 * Error message: ':displayableName is invalid'
	 *
	 * messages('test', 'Test field'); will return with array:
	 * array(
	 * 	'Test field is invalid'
	 * )
	 *
	 * messages(array('test', 'test2', array('Test field 1', 'Test field 2')); will return with array:
	 * array(
	 * 	'test' => array(
	 * 		'Test field 1 is invalid'
	 * 	),
	 * 	'test2' => array(
	 * 		'Test field 2 is invalid'
	 * 	)
	 * );
	 *
	 * @param null $dataKey
	 * @param null $displayableNames
	 * @return array|mixed
	 */
	public function messages($dataKey = null, $displayableNames = null)
	{
		// if no $dataKey specified, we return all messages we have
		if (!$dataKey)
		{
			$messages = array();
			foreach ($this->_dataStatuses as $status)
			{
				// getting displayable name
				$displayableName = $status->key;
				if (is_array($displayableNames) && isset($displayableNames[$status->key]))
					$displayableName = $displayableNames[$status->key];
				else if (is_string($displayableNames))
					$displayableName = $displayableNames;

				if (count($status->messages))
				{
					if (!isset($messages[$status->key]))
						$messages[$status->key] = array();

					foreach ($status->messages as $message)
						$messages[$status->key][] = str_replace(':displayableName', $displayableName, $message);
				}
			}

			return $messages;
		}

		// if $dataKey specified, we return messages of it
		if (!isset($this->_dataStatuses[$dataKey]))
			return array();

		// getting displayable name
		$displayableName = $dataKey;
		if (is_array($displayableNames) && isset($displayableNames[$dataKey]))
			$displayableName = $displayableNames[$dataKey];
		else if (is_string($displayableNames))
			$displayableName = $displayableNames;

		$messages = array();
		if (count($this->_dataStatuses[$dataKey]->messages))
		{
			foreach ($this->_dataStatuses[$dataKey]->messages as $message)
				$messages[] = str_replace(':displayableName', $displayableName, $message);
		}

		return $messages;
	}

	/**
	 * Gets errors for data with key $dataKey. If no $dataKey specified, gets all errors. The returned array not only
	 * contains errors, but data assigned in rule callbacks too.
	 *
	 * Return format if $dataKey is specified:
	 * array(
	 * 	'ruleKey' => array(
	 * 		'error1' => 'something',
	 * 		'assignedData' => 'other thing'
	 * 	)
	 * );
	 *
	 * Return format if no $dataKey specified:
	 * array(
	 * 	'dataKey' => array(
	 * 		'ruleKey1' => array('see above'),
	 * 		'ruleKey2' => array('see above')
	 * 	)
	 * );
	 *
	 * @param null $dataKey
	 * @return array
	 */
	public function errors($dataKey = null)
	{
		// if no $dataKey specified we return all errors
		if (!$dataKey)
		{
			$errors = array();
			foreach ($this->_dataStatuses as $status)
				$errors[$status->key] = $status->errors;

			return $errors;
		}

		// if there is no data with $dataKey, we return an empty array
		if (!isset($this->_dataStatuses[$dataKey]))
			return array();

		// return errors
		return $this->_dataStatuses[$dataKey]->errors;
	}

	/**
	 * Adds a status of $ruleKey for $dataKey data.
	 *
	 * @param string $ruleKey
	 * @param string $dataKey
	 * @param bool $valid
	 * @param array $errors
	 * @param string $message
	 * @return ValidationResult $this
	 */
	public function addStatus($ruleKey, $dataKey, $valid, $errors = array(), $message = '')
	{
		if (!isset($this->_dataStatuses[$dataKey]))
		{
			$this->_dataStatuses[$dataKey] = (object)array(
				'key' => $dataKey,
				'valid' => true,
				'errors' => array(),
				'messages' => array()
			);
		}

		$this->_dataStatuses[$dataKey]->valid = $valid ? true : false;

		$this->_dataStatuses[$dataKey]->errors[$ruleKey] = $errors;

		if ($message)
			$this->_dataStatuses[$dataKey]->messages[] = $message;

		return $this;
	}
}