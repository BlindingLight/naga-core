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
	 * @var bool debug mode
	 */
	private $_debug = false;

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

		// setting debug mode
		$this->_debug = $config->get('debug');

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

		if ($this->_debug)
		{
			$profile = new \Twig_Profiler_Profile(
				$templatePath ? $templatePath : $this->_templatePath,
				\Twig_Profiler_Profile::ROOT,
				$templatePath ? $templatePath : $this->_templatePath
			);
			$this->_twig->addExtension(new \Twig_Extension_Profiler($profile));
			$profile->enter();
			$content = $this->_twig->render($templatePath ? $templatePath : $this->_templatePath, $this->_data);
			$profile->leave();
			$dumper = new \Twig_Profiler_Dumper_Text();

			$logText = $dumper->dump($profile) . ', memory usage: '
				 . ($profile->getMemoryUsage() / 1024 / 1024) . 'MB, peak:'
				 . ($profile->getPeakMemoryUsage() / 1024 / 1024) . 'MB';

			$this->logger()->debug(str_replace(array("\n", '%'), array('', '%%'), $logText));

			return $content;
		}

		return $this->_twig->render($templatePath ? $templatePath : $this->_templatePath, $this->_data);
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