<?php

namespace Naga\Core\FileSystem;

use Naga\Core\nComponent;
use Naga\Core\Exception;

/**
 * A class that helps managing a file. It uses iFileSystem interface.
 *
 * @author BlindingLight<bloodredshade@gmail.com>
 * @package Naga\Core\FileSystem
 */
class File extends nComponent
{
	private $_baseName;
	private $_realPath;
	private $_originalPath;

	public function __construct($filePath, iFileSystem $fileSystem)
	{
		$this->registerComponent('fileSystem', $fileSystem);
		
		$this->_originalPath = $filePath;
		$this->_baseName = $this->baseName($filePath);
		$this->_realPath = $this->realPath($filePath);
	}

	/**
	 * Gets the FileSystem instance.
	 *
	 * @return iFileSystem
	 */
	private function fileSystem()
	{
		return $this->component('fileSystem');
	}

	/**
	 * Returns realPath() if you echo the instance.
	 *
	 * @return string
	 */
	public function __toString()
	{
		return $this->_realPath;
	}

	/**
	 * Returns the original path specified in constructor call.
	 *
	 * @return mixed
	 */
	public function originalPath()
	{
		return $this->_originalPath;
	}

	/**
	 * Determine if a file exists.
	 *
	 * @return bool
	 */
	public function exists()
	{
		return $this->fileSystem()->exists($this->_realPath);
	}

	/**
	 * Get the contents of a file.
	 *
	 * @return string
	 * @throws \Naga\Core\Exception\FileNotFoundException
	 */
	public function contents()
	{
		return $this->fileSystem()->get($this->_realPath);
	}

	/**
	 * Get the contents of a remote file.
	 *
	 * @return string
	 */
	public function getRemote()
	{
		return $this->fileSystem()->getRemote($this->_originalPath);
	}

	/**
	 * Get the returned value of a file.
	 *
	 * @return mixed
	 * @throws \Naga\Core\Exception\FileNotFoundException
	 */
	public function getRequire()
	{
		return $this->fileSystem()->getRequire($this->_realPath);
	}

	/**
	 * Write the contents of a file.
	 *
	 * @param string $contents
	 * @return int
	 */
	public function put($contents)
	{
		return $this->fileSystem()->put($this->_realPath, $contents);
	}

	/**
	 * Append to a file.
	 *
	 * @param string $data
	 * @return int
	 */
	public function append($data)
	{
		return $this->fileSystem()->append($this->_realPath, $data, FILE_APPEND);
	}

	/**
	 * Delete the file at a given path.
	 *
	 * @return bool
	 */
	public function delete()
	{
		return $this->fileSystem()->delete($this->_realPath);
	}

	/**
	 * Move a file to a new location. If success, originalPath, realPath and
	 * baseName will be updated.
	 *
	 * @param string $target
	 * @return bool
	 */
	public function move($target)
	{
		$success = $this->fileSystem()->move($this->_realPath, $target);
		if (!$success)
			return false;

		$this->_originalPath = $target;
		$this->_realPath = $this->realPath();
		$this->_baseName = $this->baseName($target);

		return true;
	}

	/**
	 * Copy a file to a new location.
	 *
	 * @param string $target
	 * @return bool
	 */
	public function copy($target)
	{
		return $this->fileSystem()->copy($this->_realPath, $target);
	}

	/**
	 * Extract the file extension from a file path.
	 *
	 * @return string
	 */
	public function extension()
	{
		return $this->fileSystem()->extension($this->_realPath);
	}

	/**
	 * Get the file type of a given file.
	 *
	 * @return string
	 */
	public function type()
	{
		return $this->fileSystem()->type($this->_realPath);
	}

	/**
	 * Get the file size of a given file.
	 *
	 * @return int
	 */
	public function size()
	{
		return $this->fileSystem()->size($this->_realPath);
	}

	/**
	 * Get the file's last modification time.
	 *
	 * @return int
	 */
	public function lastModified()
	{
		return $this->fileSystem()->lastModified($this->_realPath);
	}

	/**
	 * Gets file or directory real path.
	 *
	 * @return string
	 */
	public function realPath()
	{
		if (!$this->_realPath)
			$this->_realPath = $this->fileSystem()->realPath($this->_originalPath);

		return $this->_realPath;
	}

	/**
	 * Gets file or directory base name.
	 *
	 * @param null $suffix
	 * @return string
	 */
	public function baseName($suffix = null)
	{
		if (!$this->_baseName)
			$this->_baseName = $this->fileSystem()->baseName($this->_realPath, $suffix);

		return $this->_baseName;
	}

	/**
	 * Determine if the given path is a directory.
	 *
	 * @return bool
	 */
	public function isDirectory()
	{
		return $this->fileSystem()->isDirectory($this->_realPath);
	}

	/**
	 * Determine if the given path is writable.
	 *
	 * @return bool
	 */
	public function isWritable()
	{
		return $this->fileSystem()->isWritable($this->_realPath);
	}

	/**
	 * Determine if the given path is a file.
	 *
	 * @return bool
	 */
	public function isFile()
	{
		return $this->fileSystem()->isFile($this->_realPath);
	}

	/**
	 * Get an array of all files in a directory.
	 *
	 * @return array
	 */
	public function files()
	{
		return $this->fileSystem()->files($this->_realPath);
	}

	/**
	 * Get all of the files from the given directory (recursive).
	 *
	 * @return array
	 */
	public function allFiles()
	{
		return $this->fileSystem()->allFiles($this->_realPath);
	}

	/**
	 * Get all of the directories within a given directory.
	 *
	 * @return array
	 */
	public function directories()
	{
		return $this->fileSystem()->directories($this->_realPath);
	}

	/**
	 * Get all of the directories from the given directory (recursive).
	 *
	 * @return array
	 */
	public function allDirectories()
	{
		return $this->fileSystem()->allDirectories($this->_realPath);
	}

	/**
	 * Gets all files and directories from the given directory
	 *
	 * @return array
	 */
	public function filesAndDirectories()
	{
		return $this->fileSystem()->filesAndDirectories($this->_realPath);
	}

	/**
	 * Gets all files and directories from the given directory (recursive)
	 *
	 * @return array
	 */
	public function allFilesAndDirectories()
	{
		return $this->fileSystem()->allFilesAndDirectories($this->_realPath);
	}

	/**
	 * Create a directory.
	 *
	 * @param int $mode
	 * @param bool $recursive
	 * @return bool
	 */
	public function makeDirectory($mode = 0777, $recursive = false)
	{
		return $this->fileSystem()->makeDirectory($this->_originalPath, $mode, $recursive);
	}

	/**
	 * Copy a directory from one location to another. Skips at first error.
	 *
	 * @param string $destination
	 * @return bool
	 */
	public function copyDirectory($destination)
	{
		return $this->fileSystem()->copyDirectory($this->_realPath, $destination);
	}

	/**
	 * Recursively delete a directory. The directory itself can be optionally preserved.
	 *
	 * @param bool $preserve
	 * @return bool
	 */
	public function deleteDirectory($preserve = false)
	{
		return $this->fileSystem()->deleteDirectory($this->_realPath, $preserve);
	}

	/**
	 * Empty the specified directory of all files and folders.
	 *
	 * @return bool
	 */
	public function clearDirectory()
	{
		return $this->fileSystem()->deleteDirectory($this->_realPath, true);
	}
}