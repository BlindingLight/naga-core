<?php

namespace Naga\Core\Email;

interface iQueueStorage
{
	/**
	 * Adds an item to the queue. $data can be various with different storage implementations. Returns the stored item's
	 * unique key.
	 *
	 * @param string $key
	 * @param mixed $data
	 * @return string
	 */
	public function add($key, $data);

	/**
	 * Updates last try date and tries count. If tries equals to sendCount, sets unsuccessful true.
	 *
	 * @param string $key
	 * @return mixed
	 */
	public function updateLastTry($key);

	/**
	 * Removes an item from the queue storage.
	 *
	 * @param string $key
	 * @return bool
	 */
	public function remove($key);

	/**
	 * Gets an item from the queue.
	 *
	 * @param string $key
	 * @return object
	 */
	public function get($key);

	/**
	 * Gets all items that not unsuccessful.
	 *
	 * @return array
	 */
	public function pendingItems();

	/**
	 * Gets all unsuccessful items.
	 *
	 * @return array
	 */
	public function unsuccessfulItems();

	/**
	 * Gets all items.
	 *
	 * @return array
	 */
	public function allItems();

	/**
	 * Clears the queue storage.
	 *
	 * @return bool
	 */
	public function clear();
}