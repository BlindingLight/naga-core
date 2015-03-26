<?php

namespace Naga\Core\FileSystem;

interface iFileSystem
{
	/**
	 * Determine if a file exists.
	 *
	 * @param string $path
	 * @return bool
	 */
	function exists($path);

	/**
	 * Get the contents of a file.
	 *
	 * @param string $path
	 * @return string
	 */
	function get($path);

	/**
	 * Get the contents of a remote file.
	 *
	 * @param string $path
	 * @return string
	 */
	function getRemote($path);

	/**
	 * Get the returned value of a file.
	 *
	 * @param string $path
	 * @return mixed
	 */
	function getRequire($path);

	/**
	 * Write the contents of a file.
	 *
	 * @param string $path
	 * @param string $contents
	 * @return int
	 */
	function put($path, $contents);

	/**
	 * Append to a file.
	 *
	 * @param string $path
	 * @param string $data
	 * @return int
	 */
	function append($path, $data);

	/**
	 * Delete the file at a given path.
	 *
	 * @param string $path
	 * @return bool
	 */
	function delete($path);

	/**
	 * Move a file to a new location.
	 *
	 * @param string $path
	 * @param string $target
	 * @return bool
	 */
	function move($path, $target);

	/**
	 * Copy a file to a new location.
	 *
	 * @param string $path
	 * @param string $target
	 * @return bool
	 */
	function copy($path, $target);

	/**
	 * Extract the file extension from a file path.
	 *
	 * @param string $path
	 * @return string
	 */
	function extension($path);

	/**
	 * Get the file type of a given file.
	 *
	 * @param string $path
	 * @return string
	 */
	function type($path);

	/**
	 * Get the file size of a given file.
	 *
	 * @param string $path
	 * @return int
	 */
	function size($path);

	/**
	 * Get the file's last modification time.
	 *
	 * @param string $path
	 * @return int
	 */
	function lastModified($path);

	/**
	 * Gets file or directory real path.
	 *
	 * @param string $path
	 * @return string
	 */
	function realPath($path);

	/**
	 * Gets file or directory base name.
	 *
	 * @param $path
	 * @param null|string $suffix
	 * @return string
	 */
	function baseName($path, $suffix = null);

	/**
	 * Determine if the given path is a directory.
	 *
	 * @param string $directory
	 * @return bool
	 */
	function isDirectory($directory);

	/**
	 * Determine if the given path is writable.
	 *
	 * @param string $path
	 * @return bool
	 */
	function isWritable($path);

	/**
	 * Determine if the given path is a file.
	 *
	 * @param string $file
	 * @return bool
	 */
	function isFile($file);

	/**
	 * Find path names matching a given pattern.
	 *
	 * @param string $pattern
	 * @param int $flags
	 * @return array
	 */
	function glob($pattern, $flags = 0);

	/**
	 * Get an array of all files in a directory.
	 *
	 * @param string $directory
	 * @return array
	 */
	function files($directory);

	/**
	 * Get all of the files from the given directory (recursive).
	 *
	 * @param string $directory
	 * @return array
	 */
	function allFiles($directory);

	/**
	 * Get all of the directories within a given directory.
	 *
	 * @param string $directory
	 * @return array
	 */
	function directories($directory);

	/**
	 * Get all of the directories from the given directory (recursive).
	 *
	 * @param string $directory
	 * @return array
	 */
	function allDirectories($directory);

	/**
	 * Gets all files and directories from the given directory
	 *
	 * @param $directory
	 * @return array
	 */
	function filesAndDirectories($directory);

	/**
	 * Gets all files and directories from the given directory (recursive)
	 *
	 * @param $directory
	 * @return array
	 */
	function allFilesAndDirectories($directory);

	/**
	 * Create a directory.
	 *
	 * @param string $path
	 * @param int $mode
	 * @param bool $recursive
	 * @return bool
	 */
	function makeDirectory($path, $mode = 0777, $recursive = false);

	/**
	 * Copy a directory from one location to another. Skips at first error.
	 *
	 * @param string $directory
	 * @param string $destination
	 * @return bool
	 */
	function copyDirectory($directory, $destination);

	/**
	 * Recursively delete a directory. The directory itself can be optionally preserved.
	 *
	 * @param string $directory
	 * @param bool $preserve
	 * @return bool
	 */
	function deleteDirectory($directory, $preserve = false);

	/**
	 * Empty the specified directory of all files and folders.
	 *
	 * @param string $directory
	 * @return bool
	 */
	function clearDirectory($directory);
}