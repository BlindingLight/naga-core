<?php

namespace Naga\Core\Model;

class User extends Model
{
	protected $_table = 'users';

	/**
	 * Gets whether user password matches specified password.
	 *
	 * @param string $password
	 * @return bool
	 */
	public function passwordMatch($password)
	{
		$generated = $this->app()->hasher()->algorithm()->hash($password, $this->salt);

		return $generated === $password;
	}

	/**
	 * @see Model
	 */
	public function save()
	{
		parent::save();
	}

	/**
	 * @see Model
	 */
	public function delete()
	{
		parent::delete();
	}

	/**
	 * @see Model
	 */
	public function load()
	{
		return parent::load();
	}

	/**
	 * @see Model
	 */
	public function create()
	{
		return parent::create();
	}

	/**
	 * @see Model
	 */
	public function install($columns = array(), $settings = array())
	{
		parent::install(
			array_merge(
				array(
					'id' => (object)array(
					    'primary' => true,
					    'autoIncrement' => true,
						'unsigned' => true,
					),
					'username' => (object)array(
					    'type' => 'varchar',
					    'length' => '50',
					    'unique' => true,
					    'index' => 'btree',
						'null' => false
					),
					'email' => (object)array(
					    'type' => 'varchar',
						// unfortunately MySQL won't allow 255+ chars to index
						// because it assumes utf-8 chars are 3 bytes long
						// and it's limit is 767 bytes, so 256*3 = 768 :(
					    'length' => '255',
					    'unique' => true,
					    'index' => 'btree',
						'null' => false
					),
					'password' => (object)array(
					    'type' => 'varchar',
					    'length' => '40',
						'null' => false
					),
					'rememberHash' => (object)array(
					    'type' => 'varchar',
					    'length' => '40',
					    'unique' => true,
					    'index' => 'btree',
						'null' => false
					),
					'salt' => (object)array(
					    'type' => 'varchar',
					    'length' => '50',
					    'unique' => true,
					    'index' => 'btree',
						'null' => false
					),
			    ),
				$columns
			),
			$settings
		);
	}
}