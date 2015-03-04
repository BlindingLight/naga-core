<?php

namespace Naga\Core\Email;

use Naga\Core\nComponent;

class SwiftMailConnection extends nComponent implements iEmailConnection
{
	private $_connection;
	private $_sender = array();

	public function __construct($config)
	{
		if ($config->smtpAuthType == 'ssl' || $config->smtpAuthType == 'tls')
		{
			$transport = \Swift_SmtpTransport::newInstance($config->smtpHost, $config->smtpPort, $config->smtpAuthType)
						->setUsername($config->smtpUser)->setPassword($config->smtpPassword);
		}
		else
		{
			$transport = \Swift_SmtpTransport::newInstance($config->smtpHost, $config->smtpPort);
		}

		$this->_connection = \Swift_Mailer::newInstance($transport);
		$this->_sender = array($config->senderEmail => $config->senderName);
	}

	public function sendPlain(array $recipients, $subject, $message, $attachments = array(), $bcc = array())
	{
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