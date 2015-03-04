<?php

namespace Naga\Core\Hashing;

use Naga\Core\Hashing\Algorithm\Algorithm;
use Naga\Core\Hashing\Algorithm\BaseMd5;
use Naga\Core\Hashing\Algorithm\BaseSha1;
use Naga\Core\nComponent;

/**
 * Helper class for hashing.
 *
 * @author BlindingLight<bloodredshade@gmail.com>
 * @package Naga\Core\Hashing
 */
class Hasher extends nComponent
{
	public function __construct()
	{
	}

	/**
	 * Sets the algorithm instance.
	 *
	 * @param \Naga\Core\Hashing\Algorithm\Algorithm $algorithm
	 * @return \Naga\Core\Hashing\Algorithm\Algorithm
	 */
	public function setAlgorithm(Algorithm $algorithm)
	{
		$this->registerComponent('algorithm', $algorithm);
		return $this->algorithm();
	}

	/**
	 * Gets the algorithm instance.
	 *
	 * @return \Naga\Core\Hashing\Algorithm\Algorithm
	 */
	public function algorithm()
	{
		return $this->component('algorithm');
	}

	/**
	 * Generates a password hash. It's length will be the length set in the algorithm instance.
	 * This method always adds a salt to the password. If you want to change the salt length,
	 * use $hasher->algorithm()->setSaltLength(x). You can get the generated salt by calling
	 * $hasher->algorithm()->lastSalt().
	 *
	 * @param $password
	 * @return string
	 */
	public function generatePassword($password)
	{
		if (!$this->algorithm()->lastSalt())
			$this->algorithm()->generateSalt();

		return $this->algorithm()->hash($password);
	}

	/**
	 * Generates a unique token hash. Uses the algorithm's generateUnique() method.
	 *
	 * @param int $length
	 * @return string
	 */
	public function generateUniqueToken($length = 32)
	{
		return $this->algorithm()->generateUnique();
	}

	/**
	 * Gets a BaseSha1 instance.
	 *
	 * @return BaseSha1
	 */
	public function sha1()
	{
		try
		{
			return $this->component('sha1');
		}
		catch (\Exception $e)
		{
			$this->registerComponent('sha1', new BaseSha1());
			return $this->component('sha1');
		}
	}

	/**
	 * Gets a BaseMd5 instance.
	 *
	 * @return BaseMd5
	 */
	public function md5()
	{
		try
		{
			return $this->component('md5');
		}
		catch (\Exception $e)
		{
			$this->registerComponent('md5', new BaseMd5());
			return $this->component('md5');
		}
	}
}