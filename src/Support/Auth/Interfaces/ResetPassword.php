<?php

namespace Fantom\Support\Auth\Interfaces;

/**
 * ResetPassword Interface
 * This interface must be implemented by controller that is implementing
 * Forgot Password (only if developer using frameworks auth module).
 */
interface ResetPassword
{
	/**
	 * Returns new PasswordReset Model instance
	 *
	 */
	private function getPasswordResetModel();

	/**
	 * Returns URL where client will be redirected to when after successfully
	 * resetting the password.
	 */
	private function redirectTo();
}