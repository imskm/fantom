<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Fantom\Tests\Auth\FakeUser as Authenticatable;
use Fantom\Tests\Auth\FakePasswordReset as PasswordReset;
use Fantom\Support\Auth\Exceptions\TokenMissmatchException;


class UserTest extends TestCase
{
	public function testUserObjectCanBeCreated()
	{
		$this->assertInstanceOf(Authenticatable::class, new Authenticatable());
	}

	public function testFailureWhenNonExistingUserIsVerified()
	{
		$email = "no-one@test.com";

		$this->assertNull(Authenticatable::verify('email', $email));
	}

	public function testSuccessReturnsAuthenticatableInstanceForValidEmail()
	{
		/**
		 * Set environment variable myemail_address=EMAIL_ADDRESS
		 * and then export myemail_address
		 * Otherwise this test will fail always
		 */
		$email = getenv('myemail_address');
		$this->assertInstanceOf(
			Authenticatable::class,
			$user = Authenticatable::verify('email', $email)
		);

		$this->assertSame($email, $user->email);
	}

	public function testSuccessAuthenticatableCanSendPasswordResetLink()
	{
		$user = Authenticatable::verify('email', getenv('myemail_address'));

		$this->assertTrue($user->usePasswordReset(PasswordReset::class)->sendPasswordResetEmail());
		$this->assertNotEmpty($user->passwordReset()->token);
	}

	public function testSuccessAuthenticatableCanSendPasswordResetLinkObject()
	{
		$user = Authenticatable::verify('email', getenv('myemail_address'));

		$this->assertTrue($user->usePasswordReset(new PasswordReset)->sendPasswordResetEmail());
		$this->assertNotEmpty($token = $user->passwordReset()->token);
		putenv("fantom_fpasswdtok=$token");
	}

	public function testPassesForUserAccountRecoveryOnValidPostData()
	{
		$_POST = [
			'email' => getenv('myemail_address'),
			'new_password' => '87654321',
			'confirm_password' => '87654321',
			'token' => getenv("fantom_fpasswdtok"),
		];
		// @NOTE Must validate post data before calling verify
		//  See ForgotPasswordValidatorTest.
		$user = Authenticatable::verify('email', getenv('myemail_address'));
		
		$this->assertTrue($user->usePasswordReset(new PasswordReset)->resetPassword($_POST));
	}

	public function testFailureTokenMissmatchExceptionWhenInvalidTokenIsGiven()
	{
		// @NOTE Need to add theses three lines, it is needed by tests below.
		$user = Authenticatable::verify('email', getenv('myemail_address'));
		$user->usePasswordReset(new PasswordReset)->sendPasswordResetEmail();
		$token = $user->passwordReset()->token;
		putenv("fantom_fpasswdtok=$token");


		$password_plain = '87654321';
		$_POST = [
			'email' => getenv('myemail_address'),
			'new_password' => $password_plain,
			'confirm_password' => $password_plain,
			'token' => getenv("fantom_fpasswdtok") . "invalid",
		];
		// @NOTE Must validate post data before calling verify
		//  See ForgotPasswordValidatorTest.
		
		$this->expectException(TokenMissmatchException::class);

		$user->usePasswordReset(new PasswordReset)->resetPassword($_POST);

		// Assert that password of the user account has really been changed
		$this->asserTrue(password_verify($password_plain, $user->password));
	}



}