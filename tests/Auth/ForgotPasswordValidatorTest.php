<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Fantom\Support\Auth\AccountRecoveryValidator;


class ForgotPasswordValidatorTest extends TestCase
{
	public function testAuthRecoveryValidatorObjectCanBeCreated()
	{
		$this->assertInstanceOf(AccountRecoveryValidator::class, new AccountRecoveryValidator());
	}

	public function testFailsForMissingRequiredFieldAccountRecoveryPostData()
	{
		$_POST['email'] = '';

		$v = new AccountRecoveryValidator();
		$v->validateForgotPassword();

		$this->assertTrue($v->hasError());
	}

	public function testFailsForInvalidAccountRecoveryPostData()
	{
		$_POST['email'] = 'invalid_email_address';

		$v = new AccountRecoveryValidator();
		$v->validateForgotPassword();

		$this->assertTrue($v->hasError());
	}

	public function testPassesForValidPostDataForAccountRecovery()
	{
		$_POST['email'] = 'im.skm@test.com';

		$v = new AccountRecoveryValidator();
		$v->validateForgotPassword();

		$this->assertFalse($v->hasError());
	}

}