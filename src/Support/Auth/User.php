<?php

namespace Fantom\Support\Auth;

use Fantom\Token\Token;
use Fantom\Database\Model;
use Fantom\Support\Auth\PasswordReset;
use Fantom\Support\Auth\Exceptions\TokenMissmatchException;
use Fantom\Support\Auth\Interfaces\PasswordResetLinkEmailable;
use Fantom\Support\Auth\Exceptions\PasswordResetRequestNotFoundException;

/**
 * User Model class has authenticatable and recoverable functionality
 * for making it simple for user to implemnt account recovery and auth feature.
 */
abstract class User extends Model implements PasswordResetLinkEmailable
{
	/**
	 * Current PasswordReset Instance on which operations can be performed
	 *
	 * @var $password_reset  PasswordReset Model Instance
	 */
	private $password_reset;

	/**
	 * This is just instance of password_reset model that will be used to
	 * create new instance that will be used by $password_reset.
	 *
	 * @var $password_reset  PasswordReset Model Instance
	 */
	private $password_reset_object;


	public static function verify($field, $value)
	{
		return static::where($field, $value)->first();
	}

	public function sendPasswordResetEmail()
	{
		// 1. Generate password reset token
		$token = Token::generate(64);

		// 2. Save token in database
		if (!($this->storePasswordResetToken($token))) {
			return false;
		}

		// 3. Send password reset email
		return $this->sendPasswordResetLinkByEmail([
			'user' => $this,
		]);
	}

	public function storePasswordResetToken($token)
	{
		if (is_null($this->password_reset = $this->getPasswordReset())) {
			$this->password_reset = $this->makeNewPasswordReset($token);
		} else {
			$this->updatePasswordReset($this->password_reset, $token);
		}

		return $this->password_reset->save();
	}

	public function changePassword($new_password)
	{
		$this->password = password_hash($new_password, PASSWORD_DEFAULT);
		$this->updated_at = date("Y-m-d H:i:s");

		return $this->save();
	}

	public function usePasswordReset($password_reset)
	{
		if ($password_reset instanceof PasswordReset) {
			$this->password_reset_object = $password_reset;
		} else if (class_exists($password_reset)) {
			$this->password_reset_object = new $password_reset;
		} else {
			throw new \Exception("Invalid class name");
		}

		return $this;
	}

	public function getPasswordReset()
	{
		return $this->password_reset_object->byUserId($this->id);
	}

	public function makeNewPasswordReset($token)
	{
		return $this->password_reset = $this->password_reset_object->make([
			'user_id' 	=> $this->id,
			'token' 	=> $token,
		]);
	}

	public function updatePasswordReset(& $password_reset, $token)
	{
		$password_reset->{$password_reset->tokenColumnName()} = $token;
		$password_reset->created_at = date("Y-m-d H:i:s");
	}

	public function passwordReset()
	{
		return $this->password_reset;
	}

	public function resetPassword(array $data)
	{
		$this->password_reset = $this->password_reset_object->byUserId($this->id);
		if (is_null($this->password_reset)) {
			throw new PasswordResetRequestNotFoundException(
				"Account recovery request not found."
			);
		}

		// If token do not match then throw exception and return
		$tokencolname = $this->password_reset->tokenColumnName();
		if (strcmp($this->password_reset->{$tokencolname}, $data['token']) != 0) {
			throw new TokenMissmatchException("Invalid token");
		}

		$ret = $this->changePassword($data['new_password']);

		return $ret && $this->password_reset->destroyToken();
	}
}