<?php

declare(strict_types=1);

use Fantom\Router;
use PHPUnit\Framework\TestCase;
use App\Controllers\ReflectionController;

/**
 * ReflectionTest class
 */
final class ReflectionTest extends TestCase
{
	public function testReflectionParameterGetsNamespaceOfTypeHint()
	{
		$refelction = new \ReflectionMethod(
			new ReflectionController([]),
			"testReflectionMethod"
		);
		$params     = $refelction->getParameters();
		$a_param    = $params[0];

		$this->assertEquals($a_param->getClass()->name, "Fantom\View");
	}

	public function testMehodCallUsingCallUserFuncArrayGoesTrhougCallMaginMethod()
	{
		$controller = new ReflectionController([]);
		$method     = "testReflectionMethod";
		$refelction = new \ReflectionMethod(
			$controller,
			$method
		);
		$params     = $refelction->getParameters();
		$a_param    = $params[0];

		$this->expectException(\Exception::class);

		$dependency_class = $a_param->getClass()->name;
		$dependencies[] = new $dependency_class("");

		call_user_func_array([$controller, $method], $dependencies);
	}

	public function testCanReflectionReflectsMethodParamWithoutTypeHint()
	{
		$controller = new ReflectionController([]);
		$method     = "regularParamWithNoType";
		$refelction = new \ReflectionMethod(
			$controller,
			$method
		);
		$params     = $refelction->getParameters();

		$this->expectException(\Exception::class);

		$dependencies = [];
		foreach ($params as $param) {
			$dependency_class = "";
			if ($refl_class = $param->getClass()) {
				$dependency_class = $refl_class->name;
			} else {
				throw new \Exception("");
			}

			if ($dependency_class) {
				$dependencies[] = new $dependency_class();
			}
		}

		call_user_func_array([$controller, $method], $dependencies);
	}


}