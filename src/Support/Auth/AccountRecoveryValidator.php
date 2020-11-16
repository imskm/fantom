<?php

namespace Fantom\Support\Auth;

use Fantom\Validation\Validator;

/**
 * AccountRecoveryValidator
 */
class AccountRecoveryValidator extends Validator
{
	private $password_reset_table = "password_resets";
	private $password_reset_token_column = "token";
	private $password_min_length = 8;
	private $password_max_length = 16;

	public function validateForgotPassword()
	{
		$this->validate("POST", [
			"email" => "required|email",
		]);
	}

	public function validatePasswordResetToken()
	{
		$table_column = "{$this->password_reset_table},{$this->password_reset_token_column}";
		$this->validate("GET", [
			"token" => "required|min:16|max:256|exist:" . $table_column
		]);
	}

	public function validateResetPassword()
	{
		$table_column = "{$this->password_reset_table},{$this->password_reset_token_column}";
		$this->validate("GET", [
			"token" 				=> "required|min:16|max:256|exist:" . $table_column,
			"new_password" 			=> "required|min:{$this->password_min_length}|max:{$this->password_max_length}",
			"confirm_new_password" 	=> "required|confirmed:new_password",
		]);
	}
}