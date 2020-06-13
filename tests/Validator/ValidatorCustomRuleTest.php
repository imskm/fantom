<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

use Fantom\Validation\Validator;
use Fantom\Tests\Validator\ValidatorCustomRule;

/**
 * ValidatorCustomRuleTest class
 */
final class ValidatorCustomRuleTest extends TestCase
{
	public function testValidatorCustomRuleObjectCanBeCreated()
	{
		$this->assertInstanceOf(
			ValidatorCustomRule::class,
			new ValidatorCustomRule()
		);
	}

	public function testValidatorCustomRuleCanBeCalled()
	{
		$v = new ValidatorCustomRule();
		$v->validateCustom("field", "TEST_DATA");

		$this->assertFalse($v->hasError());
	}
	
}
