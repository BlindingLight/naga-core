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
	 * @var
	 */
	protected $_rootObject;

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

	/**
	 * Generates XML.
	 *
	 * @return string
	 */
	public function generateXml()
	{
		// creating root node
		if (is_object($this->_rootObject))
		{
			$xml = new \SimpleXMLElement(
				'<?xml version="' . $this->xmlVersion() . '" encoding="' . $this->xmlEncoding() . '" ?>'
				. "<{$this->_rootObject->name}>"
				. (isset($this->_rootObject->value) ? $this->_rootObject->value : '')
				. "</{$this->_rootObject->name}>"
			);

			// adding attributes to root node
			if (isset($this->_rootObject->attributes) && is_array($this->_rootObject->attributes))
			{
				foreach ($this->_rootObject->attributes as $name => $value)
					$xml->addAttribute($name, $value);
			}
		}
		else
		{
			$xml = new \SimpleXMLElement(
				'<?xml version="' . $this->xmlVersion() . '" encoding="' . $this->xmlEncoding() . '" ?><root/>'
			);
		}

		// iterating through data
		foreach ($this->toArray() as $elementName => $element)
			$this->addElements($xml, $elementName, $element);

		return $xml->asXML();
	}

	/**
	 * Sets XML's root node.
	 *
	 * @param object $rootObject
	 * @throws \Exception
	 */
	public function setRootNode($rootObject)
	{
		if (!is_object($rootObject))
			throw new \Exception('XmlResponse: Can\'t set root object, object expected, got ' . gettype($rootObject));

		if (!isset($rootObject->name))
			throw new \Exception('XmlResponse: Invalid root object passed, name property doesn\'t exist.');

		$this->_rootObject = clone $rootObject;
	}

	/**
	 * Adds elements to XML DOM.
	 *
	 * @param \SimpleXMLElement $xml
	 * @param string $name
	 * @param string|object|array $elements
	 */
	protected function addElements(\SimpleXMLElement $xml, $name, $elements)
	{
		// data is not an object nor an array so we add it as a simple node
		if (!is_object($elements) && !is_array($elements))
			$xml->addChild($name, $elements);
		// if $element is an object we get its attributes from
		// 'attributes' property and data from 'data' property
		// also we add children from 'children' property
		else if (is_object($elements))
		{
			$xml = $xml->addChild($name);
			// adding attributes
			foreach ($elements->attributes as $name => $value)
				$xml->addAttribute($name, $value);
			// adding children
			foreach ($elements->children as $name => $element)
				$this->addElements($xml, $name, $element);
		}
		else if (is_array($elements))
		{
			foreach ($elements as $name => $element)
				$this->addElements($xml, $name, $element);
		}
	}

	/**
	 * Gets XML version.
	 *
	 * @return string
	 */
	public function xmlVersion()
	{
		return $this->_xmlVersion;
	}

	/**
	 * Sets XML version.
	 *
	 * @param string $version
	 */
	public function setXmlVersion($version)
	{
		$this->_xmlVersion = $version;
	}

	/**
	 * Gets XML encoding.
	 *
	 * @return string
	 */
	public function xmlEncoding()
	{
		return $this->_xmlEncoding;
	}

	/**
	 * Sets XML encoding.
	 *
	 * @param string $encoding
	 */
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
		echo $this->generateXml();
		if ($exitAfter)
			exit();
	}
}