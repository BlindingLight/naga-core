<?php

namespace Naga\Core\Email;

use Naga\Core\Database\Connection\PostgreSQL\PgSqlConnection;
use Naga\Core\nComponent;

class QueuePostgreStorage extends nComponent implements iQueueStorage
{
	/**
	 * Construct.
	 *
	 * @param PgSqlConnection $dbConnection
	 */
	public function __construct(PgSqlConnection $dbConnection)
	{
		$this->registerComponent('dbconnection', $dbConnection);
	}

	/**
	 * Adds an item to the queue.
	 * Data format: (object)array(
	 *			'senderEmail' => $senderEmail,
	 *			'recipientEmail' => $recipientEmail,
	 *			'subject' => $subject,
	 *			'content' => $content,
	 *			'sendCount' => $sendCount,
	 * 			'type' => $type
	 *		)
	 * Returns the stored item's
	 * unique key (sha1).
	 *
	 * @param string $key
	 * @param object $data
	 * @return string
	 */
	public function add($key, $data)
	{
		try
		{
			$msg = $this->connection()->queryOne('
				insert into	"email-queue"
				("senderEmail", "recipientEmail", "subject", "content", "messageType", "lastTryDate", "messageHash")
				values
				($1, $2, $3, $4, $5, now(), $6)
				returning "messageHash"
			',
				array(
					mb_substr($data->senderEmail, 0, 265, 'UTF-8'),
					mb_substr($data->recipientEmail, 0, 265, 'UTF-8'),
					mb_substr($data->subject, 0, 200, 'UTF-8'),
					$data->content,
					isset($data->messageType) ? $data->messageType : null,
					$key ? substr($key, 0, 40) : sha1(uniqid(rand(1000,99999), true))
				)
			);

			return $msg->messageHash;
		}
		catch (\Exception $e)
		{
			return '';
		}
	}

	/**
	 * Gets an item from the queue.
	 *
	 * @param string $key
	 * @return object
	 * @throws \Exception
	 */
	public function get($key)
	{
		try
		{
			$item = $this->connection()->queryOne('
				select	"senderEmail", "recipientEmail", "subject", "content", "tries", "sendCount",
						"messageType", "messageHash"
				from	"email-queue"
				where	"messageHash" = $1
			',
				array(
					substr($key, 0, 40)
				)
			);
		}
		catch (\Exception $e)
		{
			throw new \Exception("Can't get email queue item: {$key}, doesn't exist.");
		}
	}

	/**
	 * Gets all items that not unsuccessful.
	 *
	 * @return array
	 */
	public function pendingItems()
	{
		$items = $this->connection()->query('
			select	"senderEmail", "recipientEmail", "subject", "content", "tries", "sendCount",
					"messageType", "messageHash"
			from	"email-queue"
			where	"unsuccessful" = false
		',
			array(
			)
		);

		return $items;
	}

	/**
	 * Gets all unsuccessful items.
	 *
	 * @return array
	 */
	public function unsuccessfulItems()
	{
		$items = $this->connection()->query('
			select	"senderEmail", "recipientEmail", "subject", "content", "tries", "sendCount",
					"messageType", "messageHash"
			from	"email-queue"
			where	"unsuccessful" = true
		',
			array(
			)
		);

		return $items;
	}

	/**
	 * Gets all items.
	 *
	 * @return array
	 */
	public function allItems()
	{
		$items = $this->connection()->query('
			select	"senderEmail", "recipientEmail", "subject", "content", "tries", "sendCount",
					"messageType", "messageHash"
			from	"email-queue"
		',
			array(
			)
		);

		return $items;
	}

	/**
	 * Updates last try date and tries count. If tries equals to sendCount, sets unsuccessful true.
	 *
	 * @param string $key
	 * @return mixed
	 */
	public function updateLastTry($key)
	{
		try
		{
			$item = $this->connection()->queryOne('
				select	"tries", "sendCount"
				from	"email-queue"
				where	"messageHash" = $1
			',
				array(
					substr($key, 0, 40)
				)
			);

			$unsuccessful = $item->tries + 1 >= $item->sendCount ? 't' : 'f';

			$update = $this->connection()->query('
				update	"email-queue"
				set		"lastTryDate" = now(),
						"tries" = "tries" + 1,
						"unsuccessful" = $2
				where	"messageHash" = $1
			',
				array(
					substr($key, 0, 40),
					$unsuccessful
				)
			);

			return $update ? true : false;
		}
		catch (\Exception $e)
		{
			return false;
		}
	}

	/**
	 * Removes an item from the queue storage.
	 *
	 * @param string $key
	 * @return bool
	 */
	public function remove($key)
	{
		try
		{
			$remove = $this->connection()->query('
				delete from	"email-queue"
				where		"messageHash" = $1
			',
				array(
					substr($key, 0, 40)
				)
			);

			return $remove ? true : false;
		}
		catch (\Exception $e)
		{
			return false;
		}
	}

	/**
	 * Clears the queue storage.
	 *
	 * @return bool
	 */
	public function clear()
	{
		try
		{
			$clear = $this->connection()->query('
				delete from	"email-queue"
			',
				array(

				)
			);

			return $clear ? true : false;
		}
		catch (\Exception $e)
		{
			return false;
		}
	}

	/**
	 * Returns the PgSqlConnection instance.
	 *
	 * @return PgSqlConnection
	 * @throws \Exception
	 */
	protected function connection()
	{
		try
		{
			return $this->component('dbconnection');
		}
		catch (\Exception $e)
		{
			throw new \Exception("Can't get QueuePostgreStorage's PgSqlConnection instance (db connection).");
		}
	}
}
