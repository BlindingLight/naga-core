<?php

namespace Naga\Core\Hashing\Algorithm;

/**
 * Algorithm class for basic md5 hashing.
 *
 * @author BlindingLight<bloodredshade@gmail.com>
 * @package Naga\Core\Hashing\Algorithm
 */
class BaseMd5 extends Algorithm
{
	/**
	 * Construct. Sets hash length to 32 (md5 hash length), salt length to 32 (md5 hash length).
	 */
	public function __construct()
	{
		$this->setHashLength(32);
		$this->setSaltLength(32);
	}

	/**
	 * Generates a hash from $input. If $salt is empty, lastSalt() will be used as salt,
	 * this way it can be an empty string, or generated before hashing. You can set the
	 * hash length by calling setHashLength(x).
	 *
	 * @param $input
	 * @param string $salt
	 * @param bool $raw
	 * @return string
	 */
	public function hash($input, $salt = '', $raw = false)
	{
		if ($raw)
			return md5($input . (!$salt ? $this->lastSalt() : $salt), true);

		return substr(md5($input . (!$salt ? $this->lastSalt() : $salt)), -$this->hashLength());
	}

	/**
	 * Gets a file's md5 hash.
	 *
	 * @param string $filePath
	 * @param bool $raw
	 * @return string
	 */
	public function fileHash($filePath, $raw = false)
	{
		return md5_file($filePath, !(!$raw));
	}
}