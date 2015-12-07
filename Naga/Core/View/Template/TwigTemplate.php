<?php

namespace Naga\Core\View\Template;

use Naga\Core\Application;
use Naga\Core\Collection\Map;
use Naga\Core\Config\ConfigBag;
use Naga\Core\Exception\ConfigException;

/**
 * Template implementation for generating content with Twig.
 *
 * @package Naga\Core\View\Template
 * @author  BlindingLight<bloodredshade@gmail.com>
 */
class TwigTemplate extends Map implements iTemplate
{
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
			$this->_templateRoot = $config->get('templates.root') . '/';
		else if (is_array($config) && isset($config['templateRoot']))
		{
			$this->_templateRoot =  $config['templateRoot'];
			$config = $app->config('twig');
		}
		else
		{
			$this->_templateRoot = $app->config('twig::templates.root');
			$config = $app->config('twig');
		}

		$loader = new \Twig_Loader_Filesystem($this->_templateRoot);
		$this->_twig = new \Twig_Environment(
			$loader,
			array(
				'cache' => $config->get('templates.compiled'),
				'debug' => $config->get('debug')
			)
		);

		// setting debug mode
		$this->_debug = $config->get('debug');

		// registering filters
		foreach ($app->config('twig::filters') as $filterName => $filterCallback)
			$this->registerFilter($app, $filterName, $filterCallback);

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
	 * Registers a Twig filter.
	 *
	 * @param Application $app
	 * @param string $filterName
	 * @param callable|string $filterCallback
	 */
	public function registerFilter(Application &$app, $filterName, $filterCallback)
	{
		if (is_string($filterCallback))
		{
			$this->_twig->addFilter(new \Twig_SimpleFilter($filterName, $filterCallback));
			return;
		}

		$this->_twig->addFilter(
			new \Twig_SimpleFilter(
				$filterName, function() use (&$app, $filterCallback) {
					return call_user_func_array($filterCallback, array_merge([&$app], func_get_args()));
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
		$this->add($name, $value);
	}

	/**
	 * Alias of assign().
	 *
	 * @param string $name
	 * @param mixed $value
	 */
	public function set($name, $value)
	{
		$this->add($name, $value);
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
		$data = parent::get($name);
		return $data !== null ? $data : $default;
	}

	/**
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
			$content = $this->_twig->render($templatePath ? $templatePath : $this->_templatePath, $this->toArray());
			$profile->leave();
			$dumper = new \Twig_Profiler_Dumper_Text();

			$logText = $dumper->dump($profile) . ', memory usage: '
				 . ($profile->getMemoryUsage() / 1024 / 1024) . 'MB, peak:'
				 . ($profile->getPeakMemoryUsage() / 1024 / 1024) . 'MB';

			$this->logger()->debug(str_replace(array("\n", '%'), array('', '%%'), $logText));

			return $content;
		}

		return $this->_twig->render($templatePath ? $templatePath : $this->_templatePath, $this->toArray());
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