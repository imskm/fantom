<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

use Fantom\Validation\Validator;
use Fantom\Tests\Validator\ValidatorCustomRule;

/**
 * ArrayValueTest class
 */
final class ArrayValueTest extends TestCase
{
	public function testValidatorCustomRuleObjectCanBeCreated()
	{
		$this->assertInstanceOf(Validator::class, new Validator());
	}

	public function testValidatorCanPassOnArrayValueRequiredTest()
	{
		$_POST['array'][] = 1;
		$_POST['array'][] = 2;
		$_POST['array'][] = 3;

		$v = new Validator();
		$v->validate("POST", [
			"array" => "required",
		]);

		$this->assertFalse($v->hasError());
	}
	
	public function testValidatorCanDetectEmptyArrayCommingFromPost()
	{
		$_POST['array'] = [];

		$v = new Validator();
		$v->validate("POST", [
			"array" => "required",
		]);

		$this->assertTrue($v->hasError());
	}
}
