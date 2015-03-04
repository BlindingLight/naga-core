<?php

namespace Naga\Core\Auth;

use Naga\Core\Collection\Map;

/**
 * Basic model for user.
 *
 * @author BlindingLight<bloodredshade@gmail.com>
 * @package Naga\Core\Auth
 */
class AuthUser extends Map
{
	/**
	 * Construct.
	 *
	 * @param mixed $id
	 */
	public function __construct($id)
	{
		$this->add('id', $id);
	}

	/**
	 * Gets the user's id.
	 *
	 * @return mixed
	 */
	public function id()
	{
		return $this->get('id');
	}
}