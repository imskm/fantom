<?php

namespace Fantom\Tests\Auth;

use Fantom\Support\Auth\User;
use Fantom\Tests\Auth\FakeUser;
use Fantom\Support\Auth\PasswordReset;
use Fantom\Tests\Auth\FakePasswordReset;
use Fantom\Support\Auth\Traits\SendPasswordResetEmails;

/**
 * 
 */
class FakeForgotPasswordController
{
	private $redirect_to = "/auth/login";

	use SendPasswordResetEmails;

	public function getUserModel()
	{
		return new FakeUser();
	}

	public function getPasswordResetModel()
	{
		return new FakePasswordReset();
	}

	public function redirectTo()
	{
		return $this->redirect_to;
	}

	public function sendPasswordResetLinkX()
	{
		$this->sendPasswordResetLink();
	}
}