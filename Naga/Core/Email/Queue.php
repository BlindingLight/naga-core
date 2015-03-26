<?php

namespace Naga\Core\Email;

use Naga\Core\nComponent;

class Queue extends nComponent
{
	protected $_senderConfigs = array();

	/**
	 * Construct.
	 *
	 * @param array $senderConfigs
	 */
	public function __construct(array $senderConfigs)
	{
		$this->_senderConfigs = $senderConfigs;
	}

	/**
	 * Processes the queue.
	 */
	public function processQueue()
	{
		$items = $this->storage()->pendingItems();
		foreach ($items as $item)
		{
			// trying to get sender data, if not exists, skipping
			if (!isset($this->_senderConfigs[$item->senderEmail]) && !isset($this->_senderConfigs['default']))
			{
				$this->storage()->updateLastTry($item->messageHash);
				continue;
			}

			$config = isset($this->_senderConfigs[$item->senderEmail]) ? $this->_senderConfigs[$item->senderEmail] : $this->_senderConfigs['default'];
			// we use SwiftMailerConnection for now
			$this->setEmailConnection(
				new SwiftMailConnection($config)
			);

			// updating try count & unsuccessful if tries >= sendCount
			if (!$this->emailConnection()->sendHtml(array($item->recipientEmail), $item->subject, $item->content, $item->content))
			{
				$this->storage()->updateLastTry($item->messageHash);
				continue;
			}

			// removing from queue, we sent it successfully
			$this->storage()->remove($item->messageHash);
		}
	}

	/**
	 * Adds an item (email) to the queue. Returns the added item's queue unique identifier (sha1).
	 *
	 * @param string $senderEmail
	 * @param string $recipientEmail
	 * @param string $subject
	 * @param string $content
	 * @param int $type
	 * @param int $sendCount
	 * @return string
	 * @throws \Exception
	 */
	public function add($senderEmail, $recipientEmail, $subject, $content, $type = null, $sendCount = 3)
	{
		$key = sha1($subject . $senderEmail . $recipientEmail . $content);
		$data = (object)array(
			'senderEmail' => $senderEmail,
			'recipientEmail' => $recipientEmail,
			'subject' => $subject,
			'content' => $content,
			'sendCount' => $sendCount,
			'messageType' => $type
		);

		if (!$this->storage()->add($key, $data))
			throw new \Exception("Can't add email to queue.");

		return $key;
	}

	/**
	 * Removes an item from the queue.
	 *
	 * @param $key
	 * @return bool
	 */
	public function remove($key)
	{
		return $this->storage()->remove($key) ? true : false;
	}

	/**
	 * Clears the queue.
	 *
	 * @return bool
	 */
	public function clear()
	{
		return $this->storage()->clear();
	}

	/**
	 * Sets the queue storage.
	 *
	 * @param iQueueStorage $storage
	 */
	public function setStorage(iQueueStorage $storage)
	{
		$this->registerComponent('storage', $storage);
	}

	/**
	 * Sets the email connection.
	 *
	 * @param iEmailConnection $connection
	 */
	public function setEmailConnection(iEmailConnection $connection)
	{
		$this->registerComponent('emailConnection', $connection);
	}

	/**
	 * Gets the Queue's storage instance.
	 *
	 * @return iQueueStorage
	 * @throws \Exception
	 */
	public function storage()
	{
		try
		{
			return $this->component('storage');
		}
		catch (\Exception $e)
		{
			throw new \Exception("Can't get email queue storage instance.");
		}
	}

	/**
	 * Gets the Queue's email connection instance.
	 *
	 * @return iEmailConnection
	 * @throws \Exception
	 */
	public function emailConnection()
	{
		try
		{
			return $this->component('emailConnection');
		}
		catch (\Exception $e)
		{
			throw new \Exception("Can't get email queue email connection instance.");
		}
	}
}