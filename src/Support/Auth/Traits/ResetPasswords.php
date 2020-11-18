<?php

namespace Fantom\Support\Auth\Traits;

use Fantom\Session;
use Fantom\Support\Auth\AccountRecoveryValidator;

/**
 * ResetPasswordController trait
 * This trait will be used by Controller which handles Reset Password action
 *
 */
trait ResetPasswords
{
	protected function index()
	{
		// Verify token
		$this->isTokenValid();

		$this->view->render("Auth/ResetPasswords/index.php", [
			"token" => $_GET['token']
		]);
	}

	protected function resetPassword()
	{
		$this->validate();

		$token 			= $_POST['token'];
		$new_password 	= $_POST['new_password'];
		$user 			= $this->getPasswordResetModel()->getUserByToken($token);
		$user->changePassword($new_password);

		Session::flash(
			"success",
			"Your password for account {$user->email} has been changed."
		);
		redirect($this->redirectTo());
	}

	private function isTokenValid()
	{
		$v = new AccountRecoveryValidator();
		$v->validatePasswordResetToken();
		if ($v->hasError()) {
			redirect($this->redirectTo());
		}
	}

	private function validate()
	{
		$v = new AccountRecoveryValidator();
		$v->validateResetPassword();
		if ($v->hasError()) {
			redirect($this->redirectTo());
		}
	}
}