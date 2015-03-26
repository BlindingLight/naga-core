<?php

namespace Naga\Core\FileSystem;

class UploadedFile extends File
{
	/**
	 * @var string
	 */
	private $_tempName;
	/**
	 * @var string
	 */
	private $_name;
	/**
	 * @var int
	 */
	private $_error;

	/**
	 * Construct.
	 *
	 * @param iFileSystem $fileSystem
	 * @param string $tempName
	 * @param string $name
	 * @param int $error
	 */
	public function __construct(iFileSystem $fileSystem, $tempName, $name, $error)
	{
		$this->_tempName = $tempName;
		$this->_name = $name;
		$this->_error = $error;

		parent::__construct($tempName, $fileSystem);
	}

	/**
	 * Gets the file's mime type.
	 *
	 * @return string
	 */
	public function mimeType()
	{
		$finfo = finfo_open(FILEINFO_MIME_TYPE);
		$mime = finfo_file($finfo, $this->_tempName);
		finfo_close($finfo);

		return $mime;
	}

	/**
	 * Gets the temporary name (path) of the uploaded file.
	 *
	 * @return string
	 */
	public function tempName()
	{
		return $this->_tempName;
	}

	/**
	 * Gets the original name of the uploaded file.
	 *
	 * @return string
	 */
	public function originalName()
	{
		return $this->_name;
	}

	/**
	 * Returns whether there is an error with the uploaded file.
	 *
	 * @return bool
	 */
	public function hasError()
	{
		return $this->_error != 0;
	}

	/**
	 * Gets the error code.
	 *
	 * @return int
	 */
	public function error()
	{
		return $this->_error;
	}

	/**
	 * Gets the uploaded file size in kilobytes.
	 *
	 * @return float|int
	 */
	public function size()
	{
		return parent::size() / 1024;
	}
}