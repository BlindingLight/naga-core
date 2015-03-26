<?php

namespace Naga\Core\View;

use Naga\Core\Response\HtmlResponse;
use Naga\Core\Response\JsonResponse;
use Naga\Core\Response\Response;
use Naga\Core\Response\XmlResponse;
use Naga\Core\View\Template\iTemplate;
use Naga\Core\View\Template\TwigTemplate;
use Naga\Core\nComponent;

/**
 * Base class for views.
 *
 * @package Naga\Core\View
 * @author  BlindingLight<bloodredshade@gmail.com>
 */
class View extends nComponent
{
	// response types
	const JsonResponse = 1;
	const HtmlResponse = 2;
	const XmlResponse = 3;

	/**
	 * @var \Naga\Core\View\Template\iTemplate|\Naga\Core\View\Template\TwigTemplate
	 */
	private $_template;

	/**
	 * @var \Naga\Core\Response\Response|\Naga\Core\Response\HtmlResponse|\Naga\Core\Response\JsonResponse
	 */
	private $_response;

	/**
	 * Construct.
	 *
	 * @param Response|int $response
	 * @param iTemplate $template
	 */
	public function __construct($response = self::HtmlResponse, iTemplate $template = null)
	{
		if (is_numeric($response))
		{
			switch ($response)
			{
				case self::JsonResponse:
					$response = new JsonResponse();
					break;
				case self::HtmlResponse:
					$response = new HtmlResponse();
					break;
				case self::XmlResponse:
					$response = new XmlResponse();
					break;
				default:
					$response = new HtmlResponse();
					break;
			}
		}

		$this->setResponse($response);
		if ($template)
			$this->setTemplate($template);
	}

	/**
	 * Creates a new instance with HtmlResponse.
	 *
	 * @return static
	 */
	public static function htmlView()
	{
		return new static(static::HtmlResponse);
	}

	/**
	 * Creates a new instance with HtmlResponse and TwigTemplate.
	 *
	 * @return static
	 */
	public static function htmlTwigView()
	{
		return new static(static::HtmlResponse, new TwigTemplate());
	}

	/**
	 * Creates a new instance with JsonResponse.
	 *
	 * @return static
	 */
	public static function jsonView()
	{
		return new static(static::JsonResponse);
	}

	/**
	 * Creates a new instance with JsonResponse and TwigTemplate.
	 *
	 * @return static
	 */
	public static function jsonTwigView()
	{
		return new static(static::JsonResponse, new TwigTemplate());
	}

	/**
	 * Creates a new instance with XmlResponse.
	 *
	 * @return static
	 */
	public static function xmlView()
	{
		return new static(static::XmlResponse);
	}

	/**
	 * Creates a new instance with XmlResponse and TwigTemplate.
	 *
	 * @return static
	 */
	public static function xmlTwigView()
	{
		return new static(static::XmlResponse, new TwigTemplate());
	}

	/**
	 * Executes view.
	 *
	 * @param string|null $templatePath override template path
	 * @param bool $exitAfter exit after data sent
	 */
	public function execute($templatePath = null, $exitAfter = false)
	{
		if ($this->template() && $this->_response instanceof HtmlResponse)
			$this->_response->setContent($this->template()->generate($templatePath));
		else if ($this->template() && $this->_response instanceof JsonResponse)
			$this->_response->add('content', $this->template()->generate($templatePath));

		$this->_response->send();
	}

	/**
	 * Sets the view's response object.
	 *
	 * @param \Naga\Core\Response\Response $response
	 */
	public function setResponse(Response $response)
	{
		$this->_response = $response;
	}

	/**
	 * Gets the app's resonse object.
	 *
	 * @return \Naga\Core\Response\Response|\Naga\Core\Response\HtmlResponse|\Naga\Core\Response\JsonResponse
	 */
	public function response()
	{
		return $this->_response;
	}

	/**
	 * Sets the view's template instance.
	 *
	 * @param iTemplate $template
	 */
	public function setTemplate(iTemplate $template)
	{
		$this->_template = $template;
	}

	/**
	 * Gets the view's template instance.
	 *
	 * @return iTemplate|TwigTemplate
	 */
	public function template()
	{
		return $this->_template;
	}

	/**
	 * Gets an assigned property from template.
	 *
	 * @param string $property
	 * @return mixed|null
	 */
	public function get($property)
	{
		return $this->template()->get($property);
	}

	/**
	 * Assigns a property to template.
	 *
	 * @param string $property
	 * @param mixed $value
	 * @return $this
	 */
	public function assign($property, $value)
	{
		$this->template()->add($property, $value);

		return $this;
	}

	/**
	 * Alias of assign().
	 *
	 * @param string $property
	 * @param mixed $value
	 * @throws \Naga\Core\Exception\Collection\ReadOnlyException
	 */
	public function set($property, $value)
	{
		$this->template()->add($property, $value);
	}

	/**
	 * Alias of assign().
	 *
	 * @param string $property
	 * @param mixed $value
	 * @throws \Naga\Core\Exception\Collection\ReadOnlyException
	 */
	public function add($property, $value)
	{
		$this->template()->add($property, $value);
	}
}