<?php

namespace Fantom\Support\Auth;

use Fantom\Database\Model;
use Fantom\Support\Auth\User;

/**
 * PasswordReset model
 */
class PasswordReset extends Model
{
	protected static $password_reset_token_column = "token";

	public static function make(array $data)
	{
		$pr 			= new static;
		$pr->user_id	= $data['user_id'];
		$pr->{self::$password_reset_token_column} = $data['token'];
		$pr->created_at = date("Y-m-d H:i:s");

		return $pr;
	}

	public static function byUserId($user_id)
	{
		return static::where('user_id', $user_id)->first();
	}

	public function tokenColumnName()
	{
		return self::$password_reset_token_column;
	}

	public function getUserByToken($token)
	{
		$password_reset = static::where(
			self::$password_reset_token_column,
			$token
		)->first();


		return !is_null($password_reset)?
			User::find($password_reset->user_id) : null;
	}

	public function destroyToken()
	{
		return $this->delete();
	}
}