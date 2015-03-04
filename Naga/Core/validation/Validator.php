<?php

namespace Naga\Core\Validation;

use Naga\Core\nComponent;

/**
 * Class for validating content. Thanks to Laravel team for inspiration.
 *
 * @author BlindingLight<bloodredshade@gmail.com>
 * @package Naga\Core\Validation
 */
class Validator extends nComponent
{
	/**
	 * @var array available validation rules
	 */
	private $_rules = array();

	/**
	 * @var array data to validate
	 */
	private $_data = array();

	/**
	 * @var array stores how should we validate certain data
	 */
	private $_dataValidationMap = array();

	/**
	 * Construct, adds core validation rules.
	 */
	public function __construct()
	{
		$this->addCoreRules();
	}

	/**
	 * Adds a validation rule. If there is already a rule with $name, throws an \Exception.
	 *
	 * @param string $name
	 * @param callable $callback
	 * @param string $defaultMessage
	 * @throws \Exception
	 */
	public function addRule($name, Callable $callback, $defaultMessage = '')
	{
		if (isset($this->_rules[$name]))
			throw new \Exception("Can't add {$name} validation rule, there is already on with this name.");

		$this->_rules[$name] = new Rule(
			$callback,
			$defaultMessage ? $defaultMessage : "Validation failed for {$name}."
		);
	}

	/**
	 * Gets a Rule instance from the rules list. You can alter rule messages this way ($validator->rule('x')->changeMessage('...'). If there is
	 * no rule with $name, throws an \Exception.
	 *
	 * @param string $name
	 * @return Rule
	 * @throws \Exception
	 */
	public function rule($name)
	{
		if (!isset($this->_rules[$name]))
			throw new \Exception("Can't get rule with name {$name}, doesn't exist.");

		return $this->_rules[$name];
	}

	/**
	 * Adds data and data -> rule mappings. If $rules is a string, we apply the specified rule for every item in $data.
	 * Both arrays expected to be in name => value format.
	 *
	 * Examples:
	 * make(
	 * 	array(
	 * 		'test' => 'value'
	 * 	),
	 * 	array(
	 * 		'test' => 'required|length:2:16|alpha'
	 *  )
	 * );
	 *
	 * If you want to use pipe (|) character in regexp patterns, you can do that this way:
	 * make(
	 * 	array(
	 * 		'test' => 'value'
	 * 	),
	 * 	array(
	 * 		'test' => array(
	 * 			'required',
	 * 			'length:2:16',
	 * 			'regexp:/(a|b)+/'
	 * 		)
	 * 	)
	 * );
	 * make(
	 * 	array(
	 * 		'test' => 'value'
	 * 	),
	 * 	'required|length:2:16|alpha'
	 * );
	 *
	 * @param array $data
	 * @param array|string $rules
	 * @return bool
	 * @throws \Exception
	 */
	public function make($data, $rules)
	{
		// if data is an array, we add all data
		if (is_array($data))
		{
			// note: new data overwrites existing
			foreach ($data as $name => $value)
				$this->_data[$name] = !empty($value) ? $value : null;
		}
		// maybe we'll handle closures in the future
		else
			return false;

		// create data -> rule mappings
		if (is_array($rules))
		{
			foreach ($rules as $dataKey => $ruleKey)
				$this->addDataRuleMapEntry($dataKey, $ruleKey);
		}
		// if it's just a string, we use these rules for all data
		else if (is_string($rules))
		{
			foreach ($data as $dataKey => $dataValue)
				$this->addDataRuleMapEntry($dataKey, $rules);
		}

		return true;
	}

	/**
	 * Adds a data -> rule map entries.
	 * Example:
	 * addDataRuleMapEntry('test', 'required|length:2:16');
	 *
	 * @param string $dataKey
	 * @param string $rules
	 */
	public function addDataRuleMapEntry($dataKey, $rules)
	{
		// getting rule keys & parameters
		$ruleKeys = is_array($rules) ? $rules : explode('|', $rules);

		// iterating over rules
		foreach ($ruleKeys as $key)
		{
			// getting parameters
			$params = explode(':', $key);
			$realKey = $params[0];

			// skipping if rule doesn't exist
			if (!isset($this->_rules[$realKey]))
				continue;

			// removing rule key
			array_shift($params);

			// creating map entry
			$this->_dataValidationMap[$dataKey][$realKey] = (object)array(
				'name' => $realKey,
				'params' => $params
			);
		}
	}

	/**
	 * Validates data. If $dataKey specified, it validates only data with key $dataKey.
	 *
	 * @param string $dataKey
	 * @return ValidationResult
	 */
	public function validate($dataKey = '')
	{
		$validationResult = new ValidationResult();
		// if $dataKey specified, we validate that
		if ($dataKey)
			$this->validateOne($dataKey, $validationResult);
		// if not, we validate all data
		else
		{
			foreach ($this->_dataValidationMap as $dataKey => $rules)
				$this->validateOne($dataKey, $validationResult);
		}

		return $validationResult;
	}

	/**
	 * Validates only one data entry.
	 *
	 * @param string $dataKey
	 * @param ValidationResult $validationResult
	 */
	public function validateOne($dataKey, ValidationResult &$validationResult)
	{
		if (!isset($this->_dataValidationMap[$dataKey]))
			return;

		$rules = $this->_dataValidationMap[$dataKey];

		foreach ($rules as $rule)
		{
			if (!isset($this->_rules[$rule->name]))
				return;

			// getting data
			$data = isset($this->_data[$dataKey]) ? $this->_data[$dataKey] : null;
			$errors = array();
			$params = array_merge(array($data, &$errors), $rule->params);
			$valid = call_user_func_array(
				array($this->_rules[$rule->name], '_callback'),
				$params
			);

			$message = $valid ? null : $this->createMessage($rule->name, $errors);
			$validationResult->addStatus($rule->name, $dataKey, $valid, $errors, $message);
		}
	}

	/**
	 * Creates a message from a Rule object's ->message() method's return value. You can use placeholders in this
	 * message for data set in $errors. (Example: $errors = array(':length' => 0); $message = 'Length: :length';)
	 *
	 * @param string $ruleKey
	 * @param array $errors
	 * @return string
	 */
	protected function createMessage($ruleKey, array $errors)
	{
		if (!isset($this->_rules[$ruleKey]))
			return '';

		// fix for non-string values
		$finalizedErrors = array();
		foreach ($errors as $key => $value)
		{
			if (is_array($value))
				$value = var_export($value, true);

			// converting bool values to string
			if (is_bool($value))
				$finalizedErrors[$key] = $value ? 'true' : 'false';
			else
				$finalizedErrors[$key] = $value;
		}

		return str_replace(array_keys($finalizedErrors), array_values($finalizedErrors), $this->_rules[$ruleKey]->message());
	}

	/**
	 * Adds core rules.
	 */
	private function addCoreRules()
	{
		// accepted ($data = 1 or 'on' or 'yes' or 'true' or it's boolean true)
		$this->addRule('accepted',
			function($data, &$errors)
			{
				if (is_array($data))
					return false;
				else if (is_bool($data))
					return $data;
				else
					return $data == '1' || $data == 'on' || $data == 'yes' || $data == 'true';
			},
			'You must accept :displayableName.'
		);

		// activeUrl (string is an active url, we check it with checkdnsrr)
		$this->addRule('activeUrl',
			function($data, &$errors)
			{
				$errors[':url'] = str_replace(array('http://', 'https://', 'ftp://'), '', strtolower($data));
				return checkdnsrr($errors[':url']);
			},
			':displayableName is not an active URL.'
		);

		// after:date
		$this->addRule('after',
			function($data, &$errors, $after)
			{
				if (!is_numeric($data))
					$data = strtotime($data);
				if (!is_numeric($after))
					$after = strtotime($after);

				$errors[':date'] = date('Y-m-d H:i:s', $data);
				$errors[':after'] = date('Y-m-d H:i:s', $after);

				return $data > $after;
			},
			':displayableName is before :after.'
		);

		// alpha ($data contains only alphabetic chars)
		$this->addRule('alpha',
			function($data, &$errors)
			{
				$errors[':data'] = $data;
				return preg_match('/^\pL+$/u', $data);
			},
			':displayableName contains non-alphabetic characters: :data.'
		);

		// alphaDash ($data contains only alpha-numeric chars, dashes and underscores)
		$this->addRule('alphaDash',
			function($data, &$errors)
			{
				$errors[':data'] = $data;
				return preg_match('/^[\pL\pN_-]+$/u', $data);
			},
			':displayableName must contain only alpha-numeric characters, dashes and underscores: :data.'
		);

		// alphaNum ($data contains only alpha-numeric chars)
		$this->addRule('alphaNum',
			function($data, &$errors)
			{
				$errors[':data'] = $data;
				return preg_match('/^[\pL\pN]+$/u', $data);
			},
			':displayableName must contain only alpha-numeric characters: :data.'
		);

		// before:date
		$this->addRule('before',
			function($data, &$errors, $before)
			{
				if (!is_numeric($data))
					$data = strtotime($data);
				if (!is_numeric($before))
					$before = strtotime($before);

				$errors[':date'] = date('Y-m-d H:i:s', $data);
				$errors[':before'] = date('Y-m-d H:i:s', $before);

				return $data < $before;
			},
			':displayableName is after :after.'
		);

		// between:min:max
		$this->addRule('between',
			function($data, &$errors, $min, $max)
			{
				$errors[':min'] = $min;
				$errors[':max'] = $max;
				$errors[':data'] = $data;

				if (!is_numeric($data))
					return false;

				return $data >= $min && $data <= $max;
			},
			':displayableName must be between :min and :max, but it is :data.'
		);

		// date ($data is a valid date string)
		$this->addRule('date',
			function($data, &$errors)
			{
				$errors[':date'] = $data;

				if (!is_string($data))
					return false;

				$date = date_parse($data);
				return checkdate($date['month'], $date['day'], $date['year']);
			},
			':displayableName must be a valid date.'
		);

		// dateFormat:format ($date date format must be :format)
		$this->addRule('dateFormat',
			function($data, &$errors, $format)
			{
				$errors[':date'] = $data;
				$errors[':format'] = $format;

				if (!is_string($data))
					return false;

				$info = date_parse_from_format($format, $data);
				return $info['error_count'] === 0 && $info['warning_count'] === 0;
			},
			':displayableName must be in format :format, :date given.'
		);

		// dateTime ($data is a valid datetime string)
		$this->addRule('dateTime',
			function($data, &$errors)
			{
				$errors[':date'] = $data;

				if (!is_string($data))
					return false;

				$date = date_parse($data);
				return checkdate($date['month'], $date['day'], $date['year']);
			},
			':displayableName must be a valid date and time.'
		);

		// TODO: different:field ($data must be different than :field)

		// digits:value ($data must have :value digits)
		$this->addRule('digits',
			function($data, &$errors, $value)
			{
				$digits = strlen((string)$data);
				$errors[':digits'] = $digits;
				$errors[':value'] = $value;

				return $digits == $value;
			},
			':displayableName must have :value digits, but it has :digits.'
		);

		// digitsBetween:min:max ($data must have digits between :min and :max)
		$this->addRule('digitsBetween',
			function($data, &$errors, $min, $max)
			{
				$digits = strlen((string)$data);
				$errors[':digits'] = $digits;
				$errors[':min'] = $min;
				$errors[':max'] = $max;

				return !($digits < $min || ($max && $digits > $max));
			},
			':displayableName must have :min - :max digits, but it has :digits.'
		);

		// email ($data is a valid email address)
		$this->addRule('email',
			function($data, &$errors)
			{
				return filter_var($data, FILTER_VALIDATE_EMAIL) !== false;
			},
			':displayableName is not a valid email address.'
		);

		// in:val1,val2,... ($data must be included in the given val1,val2,... list of values)
		$this->addRule('in',
			function($data, &$errors, $list)
			{
				$errors[':list'] = $list;
				$errors[':data'] = $data;

				$tmp = explode(',', $list);
				return in_array($data, $tmp);
			},
			':displayableName is not in list: :list.'
		);

		// inJsonArray:json ($data value must be in :json json array)
		$this->addRule('inJsonArray',
			function($data, &$errors, $json)
			{
				$errors[':array'] = $json;
				$errors[':data'] = $data;

				if (!$json = json_decode($json, true))
					return false;

				return in_array($data, $json);
			},
			':displayableName is not present in json array: :array.'
		);

		// inJsonObject:json (:json json object must have a property with key $data)
		$this->addRule('inJsonObject',
			function($data, &$errors, $json)
			{
				$errors[':object'] = $json;
				$errors[':data'] = $data;

				if (!$json = json_decode($json))
					return false;

				return isset($json->{$data});
			},
			':displayableName is not a property key in json object: :object.'
		);

		// TODO: inTable (I think we need to pass other type of parameters too rather than just strings)

		// int ($data must be an int type variable)
		$this->addRule('int',
			function($data, &$errors)
			{
				$errors[':type'] = gettype($data);

				return is_int($data);
			},
			':displayableName must be a type of int.'
		);

		// ip ($data must be a valid IP address)
		$this->addRule('ip',
			function($data, &$errors)
			{
				$errors[':data'] = $data;
				return filter_var($data, FILTER_VALIDATE_IP) !== false;
			},
			':displayableName must be a valid IP address.'
		);

		// length:min:max ($data's length must be between :min and :max, if :max is 0, there is no limit)
		$this->addRule('length',
			function($data, &$errors, $min = 0, $max = 0)
			{
				$len = mb_strlen($data, 'UTF-8');
				$errors[':length'] = $len;
				$errors[':min'] = $min;
				$errors[':max'] = $max;

				return !($len < $min || ($max && $len > $max));
			},
			':displayableName must be between :min - :max characters long, it has :length characters.'
		);

		// TODO: matches ($data matches an other field value)

		// max:value ($data must be less than :value, it uses (float) cast on types other than float/double, use size on arrays, length on strings)
		$this->addRule('max',
			function($data, &$errors, $max)
			{
				$errors[':data'] = $data;
				$errors[':max'] = $max;

				if (!is_numeric($data))
					return false;

				return (float)$data <= $max;
			}
		);

		// min:value ($data must have a minimum :value, it uses (float) cast on types other than float/double, use size on arrays, length on strings)
		$this->addRule('min',
			function($data, &$errors, $min)
			{
				$errors[':data'] = $data;
				$errors[':min'] = $min;

				if (!is_numeric($data))
					return false;

				return (float)$data >= $min;
			}
		);

		// notInJsonArray:json ($data value can't be in :json json array)
		$this->addRule('notInJsonArray',
			function($data, &$errors, $json)
			{
				$errors[':array'] = $json;
				$errors[':data'] = $data;

				if (!$json = json_decode($json, true))
					return false;

				return !in_array($data, $json);
			},
			':displayableName must not be in json array: :array.'
		);

		// notInJsonObject:json (:json json object must not have a property with key $data)
		$this->addRule('notInJsonObject',
			function($data, &$errors, $json)
			{
				$errors[':object'] = $json;
				$errors[':data'] = $data;

				if (!$json = json_decode($json))
					return false;

				return !isset($json->{$data});
			},
			':displayableName must not be a key of property in json object: :object.'
		);

		// notIn:val1,val2,... ($data must not be included in the given val1,val2,... list of values)
		$this->addRule('notIn',
			function($data, &$errors, $list)
			{
				$errors[':list'] = $list;
				$errors[':data'] = $data;

				$tmp = explode(',', $list);
				return !in_array($data, $tmp);
			},
			':displayableName is in list: :list.'
		);

		// numeric ($data must be numeric)
		$this->addRule('numeric',
			function($data, &$errors)
			{
				$errors[':data'] = $data;
				return is_numeric($data);
			},
			':displayableName must be numeric.'
		);

		// regexp:pattern ($data must match the given regexp pattern)
		$this->addRule('regexp',
			function($data, &$errors, $regexp)
			{
				$errors[':regexp'] = $regexp;
				$errors[':matches'] = array();
				preg_match_all($regexp, $data, $errors[':matches']);

				return !count($errors[':matches'][0]) && !isset($errors[':matches'][1])
					? false
					: count($errors[':matches'][0]) || count($errors[':matches'][1]);
			},
			':displayableName is invalid.'
		);

		// required ($data is required, basically it's not null)
		$this->addRule('required',
			function($data, &$errors)
			{
				return !is_null($data);
			},
			':displayableName is required.'
		);

		// TODO: requiredIf:field:value ($data is required if :field is equal to :value)
		// TODO: requiredWith:field1,field2,... ($data is required if all of the specified fields are present)
		// TODO: requiredWithout:field1,field2,... ($data is required if any of the specified fields are present)
		// TODO: requiredWithoutAll:field1,field2,... ($data is required if none of the specified fields are present)
		// TODO: same:field ($data is equal to :field field value)

		// size:min:max ($data size is between :min and :max, usable for arrays. If :max = 0, $data size must be exactly :min)
		$this->addRule('size',
			function($data, &$errors, $min = 0, $max = 0)
			{
				$size = !is_array($data) ? 0 : count($data);
				$errors[':size'] = $size;
				$errors[':min'] = $min;
				$errors[':max'] = $max;

				return !($size < $min || ($max && $size > $max));
			},
			':displayableName size must be between :min - :max, it is :size.'
		);

		// timestamp ($data is a valid timestamp)
		$this->addRule('timestamp',
			function($data, &$errors)
			{
				$errors[':data'] = $data;
				// basically if it's a number, it's valid, except if it's a float/double
				return is_numeric($data) && (int)$data == (string)$data;
			},
			':displayableName must be a valid timestamp.'
		);

		// url (string is a valid url)
		$this->addRule('url',
			function($data, &$errors)
			{
				$errors[':url'] = $data;
				return filter_var($data, FILTER_VALIDATE_URL) !== false;
			},
			':displayableName is not a valid URL.'
		);
	}
}