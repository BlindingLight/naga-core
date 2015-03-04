<?php

namespace Naga\Core\Hashing\Algorithm;

use Naga\Core\nComponent;

/**
 * All algorithm classes should be inherited from this class.
 *
 * @author BlindingLight<bloodredshade@gmail.com>
 * @package Naga\Core\Hashing\Algorithm
 */
abstract class Algorithm extends nComponent
{
	/**
	 * @var int
	 */
	private $_saltLength;
	/**
	 * @var int
	 */
	private $_hashLength;
	/**
	 * @var string last generated salt
	 */
	private $_lastSalt = '';

	/**
	 * Generates a hash from $input.
	 *
	 * @param $input
	 * @param string $salt
	 * @return string
	 */
	abstract public function hash($input, $salt = '');

	/**
	 * Generates a salt with length set with setSaltLength() using generateUnique().
	 *
	 * @return \Naga\Core\Hashing\Algorithm\Algorithm
	 */
	public function generateSalt()
	{
		return $this->setLastSalt(substr($this->generateUnique(), -$this->saltLength()));
	}

	/**
	 * Generates a unique hash (md5).
	 *
	 * @return string
	 */
	public function generateUnique()
	{
		return md5(uniqid(mt_rand(0, 1000), true));
	}

	/**
	 * Sets the hash length.
	 *
	 * @param $length
	 * @return \Naga\Core\Hashing\Algorithm\Algorithm
	 */
	public function setHashLength($length)
	{
		$this->_hashLength = $length;
		return $this;
	}

	/**
	 * Gets the hash length set with setHashLength().
	 *
	 * @return int
	 */
	public function hashLength()
	{
		return $this->_hashLength;
	}

	/**
	 * Sets the salt length.
	 *
	 * @param $length
	 * @return \Naga\Core\Hashing\Algorithm\Algorithm
	 */
	public function setSaltLength($length)
	{
		$this->_saltLength = $length;
		return $this;
	}

	/**
	 * Gets the salt length set with setSaltLength()
	 *
	 * @return int
	 */
	public function saltLength()
	{
		return $this->_saltLength;
	}

	/**
	 * Sets the last generated salt. lastSalt() will return with this.
	 *
	 * @param $salt
	 * @return \Naga\Core\Hashing\Algorithm\Algorithm
	 */
	protected function setLastSalt($salt)
	{
		$this->_lastSalt = $salt;
		return $this;
	}

	/**
	 * Gets the last generated salt.
	 *
	 * @return string
	 */
	public function lastSalt()
	{
		return $this->_lastSalt;
	}
}