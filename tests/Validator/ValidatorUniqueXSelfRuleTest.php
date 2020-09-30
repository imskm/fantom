<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

use Fantom\Validation\Validator;

/**
 * ValidatorUniqueXSelfTest class
 */
final class ValidatorUniqueXSelfTest extends TestCase
{
	public function testUniqueXSelfRulePassesForValidUniqueEmailInPost()
	{
		$_POST = [
			'user_id' => '2',
			'email' => 'sadaf@gmail.com',
		];

		$v = new Validator();
		$v->validate('POST', [
			'user_id' 	=> 'required|numeric|exist:users,id',
			'email' 	=> 'required|email|depends:user_id|unique_xself:users,email,id,' . post_or_empty('user_id'),
		]);

		$this->assertFalse($v->hasError());
	}

	public function testUniqueXSelfRuleFailesForNoneUniqueEmailInPost()
	{
		$_POST = [
			'user_id' => '2',
			'email' => 'wasim@gmail.com',
		];

		$v = new Validator();
		$v->validate('POST', [
			'user_id' 	=> 'required|numeric|exist:users,id',
			'email' 	=> 'required|email|depends:user_id|unique_xself:users,email,id,' . post_or_empty('user_id'),
		]);

		$this->assertTrue($v->hasError());
	}

	public function testUniqueXSelfRuleFailesWhenDependentFieldValidationFailes()
	{
		$_POST = [
			// Missing user_id field in POST to test email validation fails
			'email' => 'wasim@gmail.com',
		];

		$v = new Validator();
		$v->validate('POST', [
			'user_id' 	=> 'required|numeric|exist:users,id',
			'email' 	=> 'required|email|depends:user_id|unique_xself:users,email,id,' . post_or_empty('user_id'),
		]);

		$this->assertTrue($v->hasError());
	}
}
