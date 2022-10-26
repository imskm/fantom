<?php

namespace Fantom;

use Fantom\Middleware\Middleware as Middleware;

/**
 * Base Controller
 */
abstract class Controller
{
	/**
	 * Middleware instance
	 * @var middleware object
	 */
	protected $middleware;

	/**
	 * View instance
	 * @var resource Fantom\View
	 */
	public $view;

	/**
	 * Parameters from the matched route
	 * @var array
	 */
	protected $route_params = [];

	/**
	 * Class Constructor
	 * @param array $route_params
	 * @return void
	 */
	function __construct($route_params)
	{
		$this->route_params = $route_params;
		$this->view = new View(VIEW_PATH);
	}

	/**
	 * Magic function __call() to be called if no method is found
	 * __call() magic function will call function before() then
	 * call the called function then after() function.
	 *
	 * This is very helpful when we want to execute some code before and after
	 * a certain method call operation
	 *
	 * @param string $name The name of the method/action
	 * @param array $args The argument for the called method/action
	 * @return void
	 */
	public function __call($name, $args)
	{
		// $method = $name . "Action";
		$method = $name;

		if(method_exists($this, $method)) {
			if($this->before() !== false) {
				call_user_func_array([$this, $method], $args);
				$this->after();
			}
		} else {
			throw new \Exception("Method $method not found in controller " . get_class($this));
		}
	}

	/**
	 * Before filter - called before an action method.
	 *
	 * @return bool
	 */
	protected function before()
	{
		return false;
	}

	/**
	 * After filter - called after an action method.
	 *
	 * @return void
	 */
	protected function after() {}

	/**
	 * Middleware method - Perform Middleware action before controller action
	 */
	public function middleware($guard, $next = null)
	{
		$this->middleware = new Middleware;
		$this->middleware->guard($guard)->process();
	}
}
