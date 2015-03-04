<?php

namespace Naga\Core\Request;

use Naga\Core\FileSystem\iFileSystem;
use Naga\Core\Session\Storage\iSessionStorage;
use Naga\Core\nComponent;
use Naga\Core\Exception;

class Input extends nComponent
{
	/**
	 * @var array array containing input values of previous request
	 */
	private $_oldInput;
	/**
	 * @var array we store data here we don't want to set in $_REQUEST
	 */
	private $_tempContainer = array();

	/**
	 * Construct.
	 *
	 * @param iSessionStorage $session iSessionStorage instance for keeping input items
	 * @param iFileSystem $fileSystem iFileSystem instance for handling file uploads
	 */
	public function __construct(iSessionStorage $session, iFileSystem $fileSystem)
	{
		$this->registerComponent('session', $session);
		$this->registerComponent('fileSystem', $fileSystem);
		$old = $this->session()->get('input_keep_storage', false);
		$this->_oldInput = $old ? $old : array();
	}

	/**
	 * Gets an array containing all uploaded files. Each item is a $fileClassName class instance.
	 *
	 * @param string $fileClassName
	 * @return array
	 */
	public function files($fileClassName = '\Naga\Core\FileSystem\UploadedFile')
	{
		$files = array();
		foreach ($_FILES as $name => $file)
			$files[$name] = new $fileClassName($this->fileSystem(), $file['tmp_name'], $file['name'], $file['error']);

		return $files;
	}

	/**
	 * Gets an uploaded file as a $fileClassName class instance.
	 *
	 * @param string $fileName
	 * @param string $fileClassName
	 * @return \Naga\Core\FileSystem\UploadedFile
	 * @throws \Naga\Core\Exception\FileNotFoundException
	 */
	public function file($fileName, $fileClassName = '\Naga\Core\FileSystem\UploadedFile')
	{
		if (!isset($_FILES[$fileName]))
			throw new Exception\FileNotFoundException("Can't get uploaded file {$fileName}.");

		$file = $_FILES[$fileName];
		return new $fileClassName($this->fileSystem(), $file['tmp_name'], $file['name'], $file['error']);
	}

	/**
	 * Gets files uploaded with <input type="file" multiple>.
	 *
	 * @param string $name
	 * @param string $fileClassName
	 * @return array
	 * @throws \Naga\Core\Exception\FileNotFoundException
	 */
	public function multipleFile($name, $fileClassName = '\Naga\Core\FileSystem\UploadedFile')
	{
		if (!isset($_FILES[$name]))
			throw new Exception\FileNotFoundException("Can't get uploaded file {$name}.");

		$files = array();
		if (!isset($_FILES[$name]) || !count($_FILES[$name]['name']) || !$_FILES[$name]['name'][0])
			return $files;

		for ($i = 0, $fileCount = count($_FILES[$name]['name']); $i < $fileCount; ++$i)
		{
			$files[] = new $fileClassName(
				$this->fileSystem(),
				$_FILES[$name]['tmp_name'][$i],
				$_FILES[$name]['name'][$i],
				$_FILES[$name]['error'][$i]
			);
		}

		return $files;
	}

	/**
	 * Creates a $fileClassName class instance from the php input. It's usually used to handle
	 * ajax file uploads.
	 *
	 * @param string $fileClassName
	 * @return \Naga\Core\FileSystem\UploadedFile
	 * @throws \RuntimeException
	 */
	public function fileFromInput($fileClassName = '\Naga\Core\FileSystem\UploadedFile')
	{
		$input = fopen("php://input", "r");
		$temp = tmpfile();
		$realSize = stream_copy_to_stream($input, $temp);
		fclose($input);

		if (!isset($_SERVER['CONTENT_LENGTH']))
			throw new \RuntimeException("Can't get content length.");

		if ($realSize != (int)$_SERVER['CONTENT_LENGTH'])
			throw new \RuntimeException('Invalid file size.');

		$fileInfo = stream_get_meta_data($temp);
		return new $fileClassName($this->fileSystem(), realpath($fileInfo['uri']), basename($fileInfo['uri']), 0);
	}

	/**
	 * Gets the iFileSystem instance.
	 *
	 * @return \Naga\Core\FileSystem\iFileSystem
	 */
	protected function fileSystem()
	{
		return $this->component('fileSystem');

	}

	/**
	 * Gets the iSessionStorage instance.
	 *
	 * @return \Naga\Core\Session\Storage\iSessionStorage
	 */
	protected function session()
	{
		return $this->component('session');
	}

	/**
	 * Gets an item from the previous request's input.
	 *
	 * @param string $name
	 * @param null|mixed $default
	 * @return null|mixed
	 */
	public function old($name, $default = null)
	{
		return isset($this->_oldInput[$name]) ? $this->_oldInput[$name] : $default;
	}

	/**
	 * Keep all input items for the next request.
	 */
	public function keep()
	{
		$this->session()->set('input_keep_storage', $this->all());
	}

	/**
	 * Keeps only the items with the specified key(s) for the next request.
	 *
	 * @param string
	 */
	public function keepOnly()
	{
		$this->session()->set(
			'input_keep_storage',
			call_user_func_array(
				array($this, 'only'),
				func_get_args()
			)
		);
	}

	/**
	 * Keeps all items except the specified ones for the next request.
	 *
	 * @param mixed
	 * @return array
	 */
	public function keepExcept()
	{
		$this->session()->set(
			'input_keep_storage',
			call_user_func_array(
				array($this, 'except'),
				func_get_args()
			)
		);
	}

	/**
	 * Gets all elements from the input.
	 *
	 * @return mixed
	 */
	public function all()
	{
		return $_REQUEST;
	}

	/**
	 * Returns the items with the specified key(s).
	 *
	 * @param mixed
	 * @return array
	 */
	public function only()
	{
		$items = array();
		$args = func_get_args();
		foreach ($args as $key)
			$items[$key] = $this->get($key);

		return $items;
	}

	/**
	 * Returns all items except the specified ones.
	 *
	 * @param mixed
	 * @return array
	 */
	public function except()
	{
		$items = $this->all();
		$args = func_get_args();
		foreach ($args as $key)
		{
			if (isset($items[$key]))
				unset($items[$key]);
		}

		return $items;
	}

	/**
	 * Returns whether the input format is JSON.
	 *
	 * @return bool
	 */
	public function isJson()
	{
		return is_object(json_decode(file_get_contents('php://input')));
	}

	/**
	 * Returns the json input as an object.
	 *
	 * @return object
	 */
	public function getJson()
	{
		return json_decode(file_get_contents('php://input'));
	}

	/**
	 * Returns all input items in json format.
	 *
	 * @return string
	 */
	public function __toString()
	{
		return json_encode(array_merge($this->_tempContainer, $_REQUEST));
	}

	/**
	 * Returns whether the input has an item with the specified key.
	 *
	 * @param string $name
	 * @return bool
	 */
	public function exists($name)
	{
		return isset($_REQUEST[$name]) || isset($this->_tempContainer[$name]);
	}

	/**
	 * Gets an item from the input. If there is an item in both temporary storage and $_REQUEST, returns the item in
	 * temporary storage. (temporary storage > $_REQUEST) You can change this behavior by setting $forceRequest to true.
	 *
	 * @param string $name
	 * @param null|string $default
	 * @param bool $forceRequest check $_REQUEST for item existence first, then temporary storage (reverse default)
	 * @return null|mixed
	 */
	public function get($name, $default = null, $forceRequest = false)
	{
		if ($forceRequest && isset($_REQUEST[$name]))
			return $_REQUEST[$name];
		else if (isset($this->_tempContainer[$name]))
			return $this->_tempContainer[$name];
		else if (isset($_REQUEST[$name]))
			return $_REQUEST[$name];

		return $default;
	}

	/**
	 * Gets an item from the input as string.
	 *
	 * @param string $name
	 * @param null|string $default
	 * @param bool $forceRequest check $_REQUEST for item existence first, then temporary storage (reverse default)
	 * @return string
	 */
	public function getString($name, $default = null, $forceRequest = false)
	{
		return (string)$this->get($name, $default, $forceRequest);
	}

	/**
	 * Gets an item from the input as int.
	 *
	 * @param string $name
	 * @param null|string $default
	 * @param bool $forceRequest check $_REQUEST for item existence first, then temporary storage (reverse default)
	 * @return int
	 */
	public function getInt($name, $default = null, $forceRequest = false)
	{
		return (int)$this->get($name, $default, $forceRequest);
	}

	/**
	 * Gets an item from the input as float.
	 *
	 * @param string $name
	 * @param null|string $default
	 * @param bool $forceRequest check $_REQUEST for item existence first, then temporary storage (reverse default)
	 * @return float
	 */
	public function getFloat($name, $default = null, $forceRequest = false)
	{
		return (float)$this->get($name, $default, $forceRequest);
	}

	/**
	 * Gets an item from the input as double.
	 *
	 * @param string $name
	 * @param null|string $default
	 * @param bool $forceRequest check $_REQUEST for item existence first, then temporary storage (reverse default)
	 * @return float
	 */
	public function getDouble($name, $default = null, $forceRequest = false)
	{
		return (double)$this->get($name, $default, $forceRequest);
	}

	/**
	 * Gets an item from the input as bool.
	 *
	 * @param string $name
	 * @param null|string $default
	 * @param bool $forceRequest check $_REQUEST for item existence first, then temporary storage (reverse default)
	 * @return bool
	 */
	public function getBool($name, $default = null, $forceRequest = false)
	{
		$value = $this->get($name, $default, $forceRequest);
		return $value === true || $value == 'true' ? true : false;
	}

	/**
	 * Gets an item from the input as a json decoded object or array. If $asAssocArray is true, it returns an
	 * associative array instead of an object.
	 *
	 * @param string $name
	 * @param string $default
	 * @param bool $asAssocArray return decoded object as associative array
	 * @param bool $forceRequest
	 * @return object|array
	 */
	public function getJsonDecoded($name, $default = '{}', $asAssocArray = false, $forceRequest = false)
	{
		$value = $this->get($name, $default, $forceRequest);
		return json_decode($value, $asAssocArray);
	}

	/**
	 * Gets an item from the input and calls unserialize() on it, then returns the value.
	 *
	 * @param string $name
	 * @param null $default
	 * @param bool $forceRequest
	 * @return mixed
	 */
	public function getUnserialized($name, $default = null, $forceRequest = true)
	{
		return unserialize($this->get($name, $default, $forceRequest));
	}

	/**
	 * Stores a key - value pair. If there is an item with the specified name, throws an \Exception. If you want
	 * to store items regardless they already exist, use update() instead. Items that set this way exist per request
	 * but you can use keep(), keepOnly(), keepExcept() to keep these items too between requests.
	 *
	 * @param mixed $name
	 * @param mixed $value
	 * @param bool $tempStore
	 * @return $this
	 * @throws \Exception
	 */
	public function set($name, $value, $tempStore = false)
	{
		if ($tempStore)
		{
			if (isset($this->_tempContainer[$name]))
				throw new \Exception("Can't store input item, already exists in temporary container.");
			else
				$this->_tempContainer[$name] = $value;
		}
		else
		{
			if (isset($_REQUEST[$name]))
				throw new \Exception('Can\'t store input item, already exists in $_REQUEST.');
			else
				$this->_tempContainer[$name] = $value;
		}

		return $this;
	}

	/**
	 * Stores a key - value pair. If there is an item with the specified name then it'll be overwritten. If you don't
	 * want to replace items, use set() instead. Items that set this way exist per request
	 * but you can use keep(), keepOnly(), keepExcept() to keep these items too between requests.
	 *
	 * @param mixed $name
	 * @param mixed $value
	 * @param bool $tempStore
	 * @return $this
	 * @throws \Exception
	 */
	public function update($name, $value, $tempStore = false)
	{
		if ($tempStore)
			$this->_tempContainer[$name] = $value;
		else
			$_REQUEST[$name] = $value;

		return $this;
	}
}