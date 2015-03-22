<?php

namespace Naga\Core\View\Template;

use Naga\Core\Application;
use Naga\Core\Config\ConfigBag;
use Naga\Core\Exception\ConfigException;
use Naga\Core\nComponent;

/**
 * Template implementation for generating content with Twig.
 *
 * @package Naga\Core\View\Template
 * @author  BlindingLight<bloodredshade@gmail.com>
 */
class TwigTemplate extends nComponent implements iTemplate
{
	/**
	 * @var array assigned variables
	 */
	private $_data = array();

	/**
	 * @var \Twig_Environment Twig instance
	 */
	private $_twig;

	/**
	 * @var \Twig_Profiler_Profile
	 */
	private $_twigProfile;

	/**
	 * @var string template path
	 */
	private $_templatePath = '';

	/**
	 * @var string template root directory
	 */
	private $_templateRoot = '../app/template';

	/**
	 * Construct.
	 *
	 * @param ConfigBag|array|null $config
	 * @param Application|string|null $appInstance
	 */
	public function __construct($config = null, $appInstance = null)
	{
		// getting app instance
		if (!($appInstance instanceof Application))
			$app = is_string($appInstance) ? Application::instance($appInstance) : Application::instance();
		else
			$app = $appInstance;

		// getting config
		if ($config instanceof ConfigBag)
			$this->_templateRoot = $config->get('templates')->root . '/';
		else if (is_array($config) && isset($config['templateRoot']))
		{
			$this->_templateRoot =  $config['templateRoot'];
			$config = $app->config('application');
		}
		else
		{
			$this->_templateRoot = $app->config('application')->get('templates')->root;
			$config = $app->config('application');
		}

		$loader = new \Twig_Loader_Filesystem($this->_templateRoot);
		$this->_twig = new \Twig_Environment(
			$loader,
			array(
				'cache' => $config->get('templates')->compiled,
				'debug' => $config->get('debug')
			)
		);

		// Twig profiling if application.debug is true
		if ($config->get('debug'))
		{
			$this->_twigProfile = new \Twig_Profiler_Profile();
			$this->_twig->addExtension(new \Twig_Extension_Profiler($this->_twigProfile));
		}

		// registering localize filter
		$localization = $app->localization();
		$this->_twig->addFilter(
			new \Twig_SimpleFilter(
				'localize',
				function($constant) use(&$localization)
				{
					return $localization->get($constant);
				}
			)
		);

		$this->_twig->addFilter(
			new \Twig_SimpleFilter(
				'l',
				function($constant) use(&$localization)
				{
					return $localization->get($constant);
				}
			)
		);

		// registering url generator filter
		$urlGenerator = $app->urlGenerator();
		$this->_twig->addFilter(
			new \Twig_SimpleFilter(
				'url',
				function($route, $properties = '') use(&$urlGenerator)
				{
					return $urlGenerator->route($route, $properties, false, false);
				}
			)
		);

		// registering resource url generator filter
		$this->_twig->addFilter(
			new \Twig_SimpleFilter(
				'resource',
				function($path) use(&$urlGenerator)
				{
					return $urlGenerator->resource($path);
				}
			)
		);

		// registering ceil, floor filters
		$this->_twig->addFilter(new \Twig_SimpleFilter('ceil', 'ceil'));
		$this->_twig->addFilter(new \Twig_SimpleFilter('floor', 'floor'));

		// registering number related filters
		$this->_twig->addFilter(new \Twig_SimpleFilter('groupThousands', function($val, $decPoint = '.', $thousandSep = ',') {
				$tmp = explode('.', $val);
				$decimals = count($tmp) > 1 ? strlen($tmp[count($tmp) - 1]) : 0;
				return number_format($val, $decimals, $decPoint, $thousandSep);
			})
		);

		// registering date filter
		$this->_twig->addFilter(
			new \Twig_SimpleFilter(
				'dateFromString',
				function($date, $format = 'l, jS F, Y')
				{
					return date($format, strtotime($date));
				}
			)
		);
	}

	/**
	 * Assigns a variable that is accessible from template files.
	 *
	 * @param string $name
	 * @param mixed $value
	 */
	public function assign($name, $value)
	{
		$this->_data[$name] = $value;
	}

	/**
	 * Gets an assigned variable.
	 *
	 * @param string $name
	 * @param null $default
	 * @return null|mixed
	 */
	public function get($name, $default = null)
	{
		return isset($this->_data[$name]) ? $this->_data[$name] : $default;
	}

	/**s
	 * Generates template output.
	 *
	 * @param string|null $templatePath override template path
	 * @return string
	 * @throws ConfigException
	 */
	public function generate($templatePath = null)
	{
		if (!$this->_templatePath && !$templatePath)
			throw new ConfigException('Missing template path for view: ' . __CLASS__);

		$rendered = $this->_twig->render($templatePath ? $templatePath : $this->_templatePath, $this->_data);

		if ($this->_twigProfile instanceof \Twig_Profiler_Profile)
		{
			$dumper = new \Twig_Profiler_Dumper_Text();
			$this->logger()->debug($dumper->dump($this->_twigProfile));
			$this->logger()->dispatch();
		}

		return $rendered;
	}

	/**
	 * Sets template path.
	 *
	 * @param string $path
	 */
	public function setTemplatePath($path)
	{
		$this->_templatePath = $path;
	}
}