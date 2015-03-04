<?php

namespace Naga\Core\Email;

interface iEmailConnection
{
	function __construct($config);
	function sendPlain(array $recipients, $subject, $message, $attachments = array(), $bcc = array());
	function sendHtml(array $recipients, $subject, $message, $altBody = '', $attachments = array(), $bcc = array());
}