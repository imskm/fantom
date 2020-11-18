<?php

namespace Fantom\Support\Auth\Traits;

use Fantom\Session;
use Fantom\Support\Auth\User;
use Fantom\Support\Auth\AccountRecoveryValidator;

/**
 * Trait for forgot password controller.
 * It simplifies the account recovery implementation.
 * This trait must be used in forgot password controller.
 *
 */
trait SendPasswordResetEmails
{
	/**
	 * Index action for showing forgot password form to enter email address.
	 *
	 */
	protected function index()
	{
		$this->view->render("Auth/ForgotPassword/index.php");
	}

	protected function sendPasswordResetLink()
	{
		$this->validateEmail();
		$email = strtolower(trim($_POST['email']));
		
		if (is_null($user = $this->getUserModel()->verify('email', $email))) {
			Session::flash("error", "User with email {$emai} not found");
			redirect($this->redirectTo());
		}

		if ($user->sendPasswordResetEmail() === false) {
			Session::flash("error", $user->getLastError());
			redirect("auth/forgot-password?email={$user->email}");
		}

		redirect("auth/forgot-password/success");
	}

	protected function success()
	{
		$this->view->render("Auth/ForgotPassword/success.php");
	}

	private function validateEmail()
	{
		$v = new AccountRecoveryValidator();
		$v->validateForgotPassword();
		if ($v->hasError()) {
			redirect($this->redirectTo());
		}
	}
}
