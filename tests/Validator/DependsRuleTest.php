<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

use Fantom\Validation\Validator;

/**
 * DependsRuleTest class
 */
final class DependsRuleTest extends TestCase
{
	public function testDependsRulePassesWhenEveryDependenciesPassed()
	{
		$_POST = [
			'car_name' 		=> 'Ferrari X 123',
			'car_owner' 	=> 'Robert Downey Jr',
			'car_brand' 	=> 'Ferrari',
		];

		$v = new Validator();
		$v->validate("POST", [
			'car_name' 		=> 'required|alpha_num_space',
			'car_owner' 	=> 'required|alpha_space',
			'car_brand' 	=> 'required|depends:car_name,car_owner',
		]);

		$this->assertFalse($v->hasError());
	}

	public function testDependsRuleFailsWhenOneDependenciesFailed()
	{
		$_POST = [
			'car_name' 		=> 'Ferrari X 123',
			'car_owner' 	=> 'Robert Downey Jr.',
			'car_brand' 	=> 'Ferrari',
		];

		$v = new Validator();
		$v->validate("POST", [
			'car_name' 		=> 'required|alpha_num_space',
			'car_owner' 	=> 'required|alpha_space',
			'car_brand' 	=> 'required|depends:car_name,car_owner',
		]);

		$this->assertTrue($v->hasError());
	}
}
