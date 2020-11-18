<?php

namespace Fantom\Support\Auth\Interfaces;

/**
 * ForgotPassword Interface
 * This interface must be implemented by controller that is implementing
 * Forgot Password (only if developer using frameworks auth module).
 */
interface ForgotPassword
{
	/**
	 * Returns new User Model instance
	 *
	 */
	public function getUserModel();

	/**
	 * Returns URL where client will be redirected to when after successfully
	 * processing the forgot password request.
	 */
	public function redirectTo();
}