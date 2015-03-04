<?php

namespace Naga\Core\Util;

use Naga\Core\nComponent;

/**
 * Class for formatting text.
 *
 * @package Naga\Core\Util
 * @author  BlindingLight<bloodredshade@gmail.com>
 */
class TextFormatter extends nComponent
{
	protected $_mappings = array(
		'/##(.*?)##/' => '<h3>$1</h3>',
		'/\*\*(.*?)\*\*/' => '<strong style="font-weight: bold !important; display: inline !important;">$1</strong>',
		'/\+\+(.*?)\+\+/' => '<em style="font-style: italic !important; display: inline !important;">$1</em>',
		'/__(.*?)__/' => '<span style="text-decoration: underline !important; display: inline !important;">$1</span>',
		'/--(.*?)--/' => '<span style="text-decoration: line-through !important; display: inline !important;">$1</span>',
		'/\(youtube\)\[(.*?)\]/' => '<div class="video"><iframe width="560" height="315" src="//www.youtube.com/embed/$1" frameborder="0" allowfullscreen></iframe></div>',
		'/\(link=(.*?)\)\[(.*?)\]/' => '<a href="$2">$1</a>',
		'/\(external-link=(.*?)\)\[(.*?)\]/' => '<a href="$2" target="_blank" rel="external">$1</a>',
		'/\(link\)\[(.*?)\]/' => '<a href="$1">$1</a>',
		'/\(external-link\)\[(.*?)\]/' => '<a href="$1" target="_blank" rel="external">$1</a>',
	);

	/**
	 * Construct.
	 *
	 * @param array $mappings
	 */
	public function __construct($mappings = array())
	{
		if (count($mappings))
			$this->_mappings = $mappings;
	}

	/**
	 * Performs formatting on text.
	 *
	 * @param string $text
	 * @return string
	 */
	public function format($text)
	{
		$text = nl2br(strip_tags($text));
		$text = preg_replace(array_keys($this->_mappings), array_values($this->_mappings), $text);
		return $text;
	}
}