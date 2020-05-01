<?php

namespace Fantom;

/**
 * Router Class
 *  Core of the Framework
 *  Handles all the routing process
 */
class Router
{
	/**
	 * Associated array of routes (the routing table)
	 * @var array
	 */
	protected $routes = [];

	/**
	 * Paramaters from the matched route
	 * @var array
	 */
	protected $params = [];


	/**
	 * Controller name suffix
	 * @var string
	 */
	protected $controller_suffix = "Controller";

	/**
	 * Add a route to the routing table
	 * @param string $route The route URL
	 * @param array $params Parameters (controller, action, etc.)
	 */
	public function add($route, $params = [])
	{
		// Convert the route to a regular expression: escape forward slashes
		$route = preg_replace('/\//', '\\/', $route);

		// Convert variables e.g. {controller} => (?P<controller>[a-z-]+)
		$route = preg_replace('/\{([a-z]+)\}/', '(?P<\1>[a-z-]+)', $route);

		// Convert variables with custom regular expression e.g. {id:\d+}
		$route = preg_replace('/\{([a-z]+):([^\}]+)\}/', '(?P<\1>\2)', $route);

		// Add start and end delimiters, and case insensitive flag
		$route = '/^' . $route . '$/';

		$this->routes[$route] = $params;
	}

	/**
	 * Get all the routes from the routing table
	 *
	 * @return array
	 */
	public function getRoutes()
	{
		return $this->routes;
	}

	/**
	 * Match the route to the rotues from the routing table, setting the
	 * $params property if a route is found.
	 *
	 * @param string $url The route URL
	 * @return boolean true if match found, false otherwise
	 */
	public function match($url)
	{
		// Match to the fixed URL format /controller/action
		//$reg_exp = "/^(?P<controller>[a-z-]+)\/(?P<action>[a-z-]+)$/";

		foreach ($this->routes as $route => $params) {
			if(preg_match($route, $url, $matches)) {

				// Get the named capture group values
				foreach ($matches as $key => $match) {
					if(is_string($key))
						$params[$key] = $match;
				}

				$this->params = $params;
				return true;
			}
		}

		return false;
	}

	/**
	 * Get currently matched aramaters
	 *
	 * @return array
	 */
	public function getParams()
	{
		return $this->params;
	}

	/**
	 * Dispatcher for the route
	 * e.g. Dispatching the route to Class and Method
	 *
	 * @param string $url
	 * @return void
	 */
	public function dispatch($url)
	{
		$url = $this->removeQueryStringVariables($url);
		$url = $this->removeLastSlash($url);

		if($this->match($url)) {
			$controller = $this->params["controller"];
			$controller = $this->convertToStudlyCaps($controller);
			//$controller = "App\Controllers\\$controller";
			$controller = $this->getNamespace()
					. $controller
					. $this->controller_suffix;

			if(class_exists($controller)) {
				$controller_obj = new $controller($this->params);

				$action = $this->params["action"];
				$action = $this->convertToCamelCase($action);

				if(is_callable([$controller_obj, $action])) {
					$dependencies = $this->resolveDependency($controller_obj, $action);
					call_user_func_array([$controller_obj, $action], $dependencies);
				} else {
					throw new \Exception("Method $action (in cotroller $controller) not found.");
				}

			} else {
				throw new \Exception("Controller Class $controller not found.");
			}

		} else {
			throw new \Exception("No route found for the url $url", 404);
		}

		return true;
	}

	/**
	 * Convert the string with hyphen to SudlyCaps,
	 * e.g. post-authors => PostAuthors
	 *
	 * @param string $string The string to convert
	 * @return string
	 */
	protected function convertToStudlyCaps($string)
	{
		return str_replace(" ", "", ucwords(str_replace("-", " ", $string)));
	}

	/**
	 * Convert the string with hyphen to camelCase,
	 * e.g. add-new => addNew
	 *
	 * @param string $string The string to convert
	 * @return string
	 */
	protected function convertToCamelCase($string)
	{
		return lcfirst($this->convertToStudlyCaps($string));
	}

	/**
	 * Removing variables from URL to match the route
	 * e.g. localhost/posts/index?page=2&view=print 	=>	/posts/index
	 *
	 * @param string $url the full URL
	 * @return string The URL with removed variables
	 */
	protected function removeQueryStringVariables($url)
	{
		if($url != "") {
			$parts = explode("&", $url, 2);
			if(strpos($parts[0], "=") === false) {
				$url = $parts[0];
			} else {
				$url = "";
			}
		}

		return $url;
	}

	/**
	 * Get the namespace for the controller class. The namespace defined
	 * in the route parameters is added if present
	 *
	 * @return string The requested URL
	 */
	protected function getNamespace()
	{
		$namespace = "App\Controllers\\";

		if(array_key_exists("namespace", $this->params)) {
			$namespace .= $this->params["namespace"] . "\\";
		}

		return $namespace;
	}

	/**
	 * Removing the last slash from URL in order to match the route
	 * e.g. posts/			=>		posts
	 *		posts/index/	=>		posts/index
	 *
	 * @return string The requested URL
	 */
	protected function removeLastSlash($url)
	{
		$url = rtrim($url);
		$url = preg_replace('/\/$/', '', $url);

		return $url;
	}

	/**
	 * Resolves dependency of a method and returns all the parameters in array.
	 *
	 * @param $instance  object of a class (the controller class)
	 * @param $metod     string  
	 */
	private function resolveDependency($instance, $method)
	{
		$refelction = new \ReflectionMethod($instance, $method);
		$params     = $refelction->getParameters();
		
		$dependencies = [];
		foreach ($params as $param) {

			$dependency_class = "";
			if ($refl_class = $param->getClass()) {
				$dependency_class = $refl_class->name;
			} else {
				throw new \Exception(
					"Can not resolve dependency for {$method}, no type hint found."
				);
			}

			// TODO: This is not correct. $dependency_class class may depend on
			//       other class and it too need to be resolved recursively.
			$dependencies[] = new $dependency_class;
		}

		return $dependencies;
	}
}
