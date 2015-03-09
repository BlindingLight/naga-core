<?php

namespace Naga\Core\Email;

use Naga\Core\Exception\ConfigException;
use Naga\Core\nComponent;

class SwiftMailConnection extends nComponent implements iEmailConnection
{
	protected $_connection;
	protected $_sender = array();
	protected $_config;

	public function __construct($config)
	{
		if (!is_object($config))
			throw new ConfigException('SwiftMailerConnection got invalid configuration.');

		$this->_config = clone $config;
		$this->_sender = array($config->senderEmail => $config->senderName);
	}

	protected function createSwiftMailerInstance()
	{
		if ($this->_connection)
			return;

		if ($this->_config->smtpAuthType == 'ssl' || $this->_config->smtpAuthType == 'tls')
		{
			$transport = \Swift_SmtpTransport::newInstance(
				$this->_config->smtpHost,
				$this->_config->smtpPort,
				$this->_config->smtpAuthType
			)->setUsername($this->_config->smtpUser)->setPassword($this->_config->smtpPassword);
		}
		else
		{
			$transport = \Swift_SmtpTransport::newInstance($this->_config->smtpHost, $this->_config->smtpPort);
		}

		$this->_connection = \Swift_Mailer::newInstance($transport);
	}

	public function sendPlain(array $recipients, $subject, $message, $attachments = array(), $bcc = array())
	{
		$this->createSwiftMailerInstance();

		$msg = \Swift_Message::newInstance();
		$msg->setSubject($subject);
		$msg->setFrom($this->_sender);
		$msg->setTo($recipients);
		$msg->setBody($message, 'text/plain');
		$msg->setBcc($bcc);

		foreach ($attachments as $path => $fileName)
		{
			if (is_numeric($path))
				$msg->attach(\Swift_Attachment::fromPath($fileName));
			else
				$msg->attach(\Swift_Attachment::fromPath($path)->setFilename($fileName));
		}

		return $this->_connection->send($msg);
	}

	public function sendHtml(array $recipients, $subject, $message, $altBody = '', $attachments = array(), $bcc = array())
	{
		$this->createSwiftMailerInstance();

		$msg = \Swift_Message::newInstance();
		$msg->setSubject($subject);
		$msg->setFrom($this->_sender);
		$msg->setTo($recipients);
		$msg->setBody($message, 'text/html');
		$msg->addPart($altBody, 'text/plain');
		$msg->setBcc($bcc);

		foreach ($attachments as $path => $fileName)
		{
			if (is_numeric($path))
				$msg->attach(\Swift_Attachment::fromPath($fileName));
			else
				$msg->attach(\Swift_Attachment::fromPath($path)->setFilename($fileName));
		}

		return $this->_connection->send($msg);
	}
}