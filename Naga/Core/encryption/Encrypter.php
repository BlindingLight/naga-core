<?php

namespace Naga\Core\Encryption;

use Naga\Core\Encryption\Algorithm\Algorithm;
use Naga\Core\nComponent;

/**
 * Helper class for encryption.
 *
 * @author BlindingLight<bloodredshade@gmail.com>
 * @package Naga\Core\Encryption
 */
class Encrypter extends nComponent
{
	public function __construct()
	{
	}

	/**
	 * Sets the encryption algorithm and returns it.
	 *
	 * @param \Naga\Core\Encryption\Algorithm\Algorithm $algorithm
	 * @return \Naga\Core\Encryption\Algorithm\Algorithm
	 */
	public function setAlgorithm(Algorithm $algorithm)
	{
		$this->registerComponent('algorithm', $algorithm);
		return $this->algorithm();
	}

	/**
	 * Gets the algorithm instance.
	 *
	 * @return \Naga\Core\Encryption\Algorithm\Algorithm
	 */
	public function algorithm()
	{
		return $this->component('algorithm');
	}
}