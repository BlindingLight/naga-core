<?php

namespace Naga\Core\FileSystem;

use Naga\Core\Exception;
use Naga\Core\nComponent;

class FileSystem extends nComponent implements iFileSystem
{
	/**
	 * Determine if a file exists.
	 *
	 * @param string $path
	 * @return bool
	 */
	public function exists($path)
	{
		return file_exists($path);
	}

	/**
	 * Get the contents of a file.
	 *
	 * @param string $path
	 * @return string
	 * @throws \Naga\Core\Exception\FileNotFoundException
	 */
	public function get($path)
	{
		if (!$this->isFile($path))
			throw new Exception\FileNotFoundException("File doesn't exists: '$path'");

		return file_get_contents($path);
	}

	/**
	 * Get the contents of a remote file.
	 *
	 * @param string $path
	 * @return string
	 */
	public function getRemote($path)
	{
		return file_get_contents($path);
	}

	/**
	 * Get the returned value of a file.
	 *
	 * @param string $path
	 * @return mixed
	 * @throws \Naga\Core\Exception\FileNotFoundException
	 */
	public function getRequire($path)
	{
		if (!$this->isFile($path))
			throw new Exception\FileNotFoundException("File doesn't exists: '$path'");

		return require $path;
	}

	/**
	 * Write the contents of a file.
	 *
	 * @param string $path
	 * @param string $contents
	 * @return int
	 */
	public function put($path, $contents)
	{
		return file_put_contents($path, $contents);
	}

	/**
	 * Append to a file.
	 *
	 * @param string $path
	 * @param string $data
	 * @return int
	 */
	public function append($path, $data)
	{
		return file_put_contents($path, $data, FILE_APPEND);
	}

	/**
	 * Delete the file at a given path.
	 *
	 * @param string $path
	 * @return bool
	 */
	public function delete($path)
	{
		return unlink($path);
	}

	/**
	 * Move a file to a new location.
	 *
	 * @param string $path
	 * @param string $target
	 * @return bool
	 */
	public function move($path, $target)
	{
		return rename($path, $target);
	}

	/**
	 * Copy a file to a new location.
	 *
	 * @param string $path
	 * @param string $target
	 * @return bool
	 */
	public function copy($path, $target)
	{
		return copy($path, $target);
	}

	/**
	 * Extract the file extension from a file path.
	 *
	 * @param string $path
	 * @return string
	 */
	public function extension($path)
	{
		return pathinfo($path, PATHINFO_EXTENSION);
	}

	/**
	 * Get the file type of a given file.
	 *
	 * @param string $path
	 * @return string
	 */
	public function type($path)
	{
		return filetype($path);
	}

	/**
	 * Get the file size of a given file.
	 *
	 * @param string $path
	 * @return int
	 */
	public function size($path)
	{
		return filesize($path);
	}

	/**
	 * Get the file's last modification time.
	 *
	 * @param string $path
	 * @return int
	 */
	public function lastModified($path)
	{
		return filemtime(realpath($path));
	}

	/**
	 * Gets file or directory real path.
	 *
	 * @param string $path
	 * @return string
	 */
	public function realPath($path)
	{
		return realpath($path);
	}

	/**
	 * Gets file or directory base name.
	 *
	 * @param $path
	 * @param null|string $suffix
	 * @return string
	 */
	public function baseName($path, $suffix = null)
	{
		return basename($path, $suffix);
	}

	/**
	 * Determine if the given path is a directory.
	 *
	 * @param string $directory
	 * @return bool
	 */
	public function isDirectory($directory)
	{
		return is_dir($directory);
	}

	/**
	 * Determine if the given path is writable.
	 *
	 * @param string $path
	 * @return bool
	 */
	public function isWritable($path)
	{
		return is_writable($path);
	}

	/**
	 * Determine if the given path is a file.
	 *
	 * @param string $file
	 * @return bool
	 */
	public function isFile($file)
	{
		return is_file($file);
	}

	/**
	 * Find path names matching a given pattern.
	 *
	 * @param string $pattern
	 * @param int $flags
	 * @return array
	 */
	public function glob($pattern, $flags = 0)
	{
		return glob($pattern, $flags);
	}

	/**
	 * Get an array of all files in a directory.
	 *
	 * @param string $directory
	 * @return array
	 */
	public function files($directory)
	{
		$dir = $this->glob($directory . '/*');

		if ($dir === false)
			return array();

		$t = &$this;
		return array_filter($dir, function($file) use(&$t)
		{
			return $t->isFile($file);
		});
	}

	/**
	 * Get all of the files from the given directory (recursive).
	 *
	 * @param string $directory
	 * @return array
	 */
	public function allFiles($directory)
	{
		if (!$this->isDirectory($directory))
			return array();

		$files = $this->files($directory);
		$tmp = $this->directories($directory);

		foreach ($tmp as $entry)
			$files = array_merge($files, $this->allFiles($entry));

		return $files;
	}

	/**
	 * Get all of the directories within a given directory.
	 *
	 * @param string $directory
	 * @return array
	 */
	public function directories($directory)
	{
		$dir = $this->glob($directory . '/*');

		if ($dir === false)
			return array();

		$t = &$this;
		return array_filter($dir, function($file) use(&$t)
		{
			return $t->isDirectory($file);
		});
	}

	/**
	 * Get all of the directories from the given directory (recursive).
	 *
	 * @param string $directory
	 * @return array
	 */
	public function allDirectories($directory)
	{
		if (!$this->isDirectory($directory))
			return array();

		$tmp = $this->directories($directory);
		$directories = $tmp;

		foreach ($tmp as $entry)
			$directories = array_merge($directories, $this->allDirectories($entry));

		return $directories;
	}

	/**
	 * Gets all files and directories from the given directory
	 *
	 * @param $directory
	 * @return array
	 */
	public function filesAndDirectories($directory)
	{
		if (!$this->isDirectory($directory))
			return array();

		return array_merge($this->files($directory), $this->directories($directory));
	}

	/**
	 * Gets all files and directories from the given directory (recursive)
	 *
	 * @param $directory
	 * @return array
	 */
	public function allFilesAndDirectories($directory)
	{
		if (!$this->isDirectory($directory))
			return array();

		$entries = array_merge($this->files($directory), $this->directories($directory));

		$tmp = array();
		foreach ($entries as $entry)
			$tmp = array_merge($tmp, $this->allFilesAndDirectories($entry));

		return array_merge($entries, $tmp);
	}

	/**
	 * Create a directory.
	 *
	 * @param string $path
	 * @param int $mode
	 * @param bool $recursive
	 * @return bool
	 */
	public function makeDirectory($path, $mode = 0777, $recursive = false)
	{
		return mkdir($path, $mode, $recursive);
	}

	/**
	 * Copy a directory from one location to another. Skips at first error.
	 *
	 * @param string $directory
	 * @param string $destination
	 * @return bool
	 */
	public function copyDirectory($directory, $destination)
	{
		if (!$this->isDirectory($directory))
			return false;

		// create destination directory
		if (!$this->isDirectory($destination))
			$this->makeDirectory($destination, 0777, true);

		$entries = $this->filesAndDirectories($directory);
		foreach ($entries as $entry)
		{
			$target = $destination . '/' . $this->baseName($entry);
			$dest = $this->realPath($entry);

			// if it's a directory, we call this method again (nesting yeah)
			if ($this->isDirectory($entry))
			{
				if (!$this->copyDirectory($dest, $target))
					return false;
			}
			// if it's a file, we just simply copy it
			else
			{
				if (!$this->copy($dest, $target))
					return false;
			}
		}

		return true;
	}

	/**
	 * Recursively delete a directory. The directory itself can be optionally preserved.
	 *
	 * @param string $directory
	 * @param bool $preserve
	 * @return bool
	 */
	public function deleteDirectory($directory, $preserve = false)
	{
		if (!$this->isDirectory($directory))
			return false;

		$entries = $this->allFilesAndDirectories($directory);

		foreach ($entries as $entry)
		{
			$realpath = $this->realPath($entry);

			// unknown bug, there is an empty string here (some wtf with realpath?)
			if (!$realpath)
				continue;

			// if it's a directory, we call this method again (nesting yeah)
			if ($this->isDirectory($realpath))
			{
				if (!$this->deleteDirectory($realpath))
					return false;
			}
			// if it's a file, we just simply delete it
			else
			{
				if (!$this->delete($realpath))
					return false;
			}
		}

		if (!$preserve)
			return rmdir($directory);

		return true;
	}

	/**
	 * Empty the specified directory of all files and folders.
	 *
	 * @param string $directory
	 * @return bool
	 */
	public function clearDirectory($directory)
	{
		return $this->deleteDirectory($directory, true);
	}
}