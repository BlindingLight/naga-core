<?php

namespace Naga\Core\Middleware;

class Csrf extends Middleware
{
	/**
	 * Subscribes to events.
	 */
	public function subscribe()
	{
		$this->app()->events()->listen('csrf.check', $this);
	}

	/**
	 * Checks whether CSRF token in input matches token stored in session.
	 *
	 * @return bool
	 * @throws \Exception
	 */
	public function handle()
	{
		$match = $this->checkCsrfMatch($this->app()->config('application::csrfTokenInputName'));
		$this->refreshCsrfToken();

		if (!$match)
			throw new \Exception('CSRF token mismatch.');

		return true;
	}

	/**
	 * Refreshes (regenerates) CSRF token.
	 */
	protected function refreshCsrfToken()
	{
		$this->app()->session()->set('_csrfToken', md5(uniqid(microtime(true), true)));
	}

	/**
	 * Checks whether input with name $inputName matches stored CSRF token.
	 *
	 * @param string $inputName
	 * @return bool
	 */
	public function checkCsrfMatch($inputName = '_csrfToken')
	{
		return !$this->app()->input()->exists($inputName) ||
			$this->app()->input()->getString($inputName) == (string)$this->app()->session()->get('_csrfToken');
	}
}