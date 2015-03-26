<?php

namespace Naga\Core\Response;

class XmlResponse extends Response
{
	/**
	 * @var string xml version
	 */
	private $_xmlVersion;
	/**
	 * @var string xml encoding
	 */
	private $_xmlEncoding;
	/**
	 * @var \DOMDocument DOMDocument instance for xml creation
	 */
	private $_dom;

	/**
	 * XmlResponse construct. Use it as a Map construct, also you can specify xml version and encoding.
	 *
	 * @param array $data
	 * @param bool $readOnly
	 * @param string $xmlVersion
	 * @param string $xmlEncoding
	 */
	public function __construct($data = array(), $readOnly = false, $xmlVersion = '1.0', $xmlEncoding = 'UTF-8')
	{
		parent::__construct($data, $readOnly);
		$this->setXmlVersion($xmlVersion);
		$this->setXmlEncoding($xmlEncoding);
		$this->setMimeType('text/xml');
	}

	public function generateXml()
	{

	}

	public function xmlVersion()
	{
		return $this->_xmlVersion;
	}

	public function setXmlVersion($version)
	{
		$this->_xmlVersion = $version;
	}

	public function xmlEncoding()
	{
		return $this->_xmlEncoding;
	}

	public function setXmlEncoding($encoding)
	{
		$this->_xmlEncoding = $encoding;
	}

	/**
	 * Same as \Naga\Core\Collection\Map toArray().
	 *
	 * @return mixed
	 */
	public function data()
	{
		return $this->toArray();
	}

	/**
	 * Echoes the xml output, and if $exitAfter is true, calls exit().
	 *
	 * @param bool $exitAfter
	 */
	public function send($exitAfter = false)
	{
		header('Content-type: ' . $this->mimeType());
		//echo \Naga\Core\Util\Xml::encode($this->data());
		// TODO: xml output
		if ($exitAfter)
			exit();
	}
}