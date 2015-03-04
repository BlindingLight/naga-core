<?php

namespace Naga\Core\View\Template;

interface iTemplate
{
	function generate();
	function assign($name, $value);
	function get($name, $default = null);
	function setTemplatePath($path);
}