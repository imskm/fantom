<?php

declare(strict_types=1);

use Fantom\Router;
use PHPUnit\Framework\TestCase;

/**
 * RouterTest class
 */
final class RouterTest extends TestCase
{
	public function testRouterObjectCanBeCreated()
	{
		$this->assertInstanceOf(Router::class, new Router());
	}

	public function testRouterCanMatchBasicUri()
	{
		$uri = "home/index";
		$router = new Router();
		$router->add($uri, ['controller' => 'Home', 'action' => 'index']);

		$this->assertTrue($router->match($uri));
	}

	public function testRouterCanMatchDynamicUri()
	{
		$uri = "home/index";
		$router = new Router();
		$router->add('{controller}/{action}');

		$this->assertTrue($router->match($uri));
	}

	public function testRouterCanMatchDynamicUriWithRouteParam()
	{
		$uri = "blog/2/show";
		$router = new Router();
		$router->add('{controller}/{id:\d+}/{action}');

		$this->assertTrue($router->match($uri));
	}

	public function testRouterMatchFailsOnDynamicUriHavingWrongRouteParamDataType()
	{
		$uri = "blog/x/show";
		$router = new Router();
		$router->add('{controller}/{id:\d+}/{action}');

		$this->assertFalse($router->match($uri));
	}

	public function testRouterMatchCanWorkWithNamespace()
	{
		$uri = "admin/blog/1/show";
		$router = new Router();
		$router->add('admin/{controller}/{id:\d+}/{action}', ['namespace' => 'Admin']);

		$this->assertTrue($router->match($uri));
	}

	public function testRouterCanMatchFromMultipleAddedRoutes()
	{
		$uri = "blog/x/show";
		$router = new Router();
		$router->add('', ['controller' => 'Home', 'action' => 'index']);
		$router->add('{controller}/{action}');
		$router->add('{controller}/{id:\d+}/{action}');

		$this->assertFalse($router->match($uri));
	}

	public function testRouterDispatchCanDispatchRequestToExistingRoute()
	{
		$uri = "home/index";
		$router = new Router();
		$router->add('', ['controller' => 'Home', 'action' => 'index']);
		$router->add('{controller}/{action}');

		$this->assertTrue($router->dispatch($uri));
	}

	public function testRouterDispatchAssertionFailsOnInvalidRoute()
	{
		$uri = "home/1/index";
		$router = new Router();
		$router->add('', ['controller' => 'Home', 'action' => 'index']);
		$router->add('{controller}/{action}');

		$this->expectException(\Exception::class);
		$router->dispatch($uri);
	}

	public function testCanNotDispatchWhenNonExistingRouteProvided()
	{
		$uri = "home/does-not-exist";
		$router = new Router();
		$router->add('', ['controller' => 'Home', 'action' => 'index']);
		$router->add('{controller}/{action}');
		$router->add('{controller}/{id:\d+}/{action}');

		$this->expectException(\Exception::class);
		$router->dispatch($uri);
	}

	public function testRouterCanNotResolveMethodDependencyWhichHasNoTypeHintParam()
	{
		$uri = "home/method-with-no-type-hint";
		$router = new Router();
		$router->add('', ['controller' => 'Home', 'action' => 'index']);
		$router->add('{controller}/{action}');
		$router->add('{controller}/{id:\d+}/{action}');

		$this->expectException(\Exception::class);
		$router->dispatch($uri);
	}

	
}