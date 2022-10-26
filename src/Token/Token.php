<?php

namespace Fantom\Token;

/**
* Token class
* handles : Token generation, get, set, check
*/
class Token
{
	/**
	 * Token generator
	 * @var int $size  size of the token
	 * @return string  generated token
	 */
	public static function generate($size = 32)
	{
		return bin2hex(openssl_random_pseudo_bytes($size));
	}

	/**
	 * Sets token with a name (as key value pair)
	 * @var string  Name of the token
	 * @var string  Token
	 * @return void
	 */
	public static function set($token_name, $token)
	{
		$_SESSION[$token_name] = $token;
	}

	/**
	 * Returns token of the asked token
	 * @var string  Token name
	 * @return string  Token if exist else NULL
	 */
	public static function get($token_name)
	{
		return self::exist($token_name)? $_SESSION[$token_name] : null;
	}

	/**
	 * Verifies given token with token that is stored in sesstion
	 * @var string  Token
	 * @var string  Token name / key
	 * @return bool
	 */
	public static function check($token, $token_name)
	{
		if(self::exist($token_name))
			return $_SESSION[$token_name] === $token;

		return false;
	}

	/**
	 * Checks if the given token exist in the session or not
	 * @var string  Token name / key
	 * @return bool
	 */
	public static function exist($token_name)
	{
		return array_key_exists($token_name, $_SESSION);
	}

	/**
	 * Destroy token
	 * @var string  Token name / key
	 * @return bool
	 */
	public static function destroy($token_name)
	{
		if(!self::exist($token_name)) {
			return false;
		}
		unset($_SESSION[$token_name]);

		return true;
	}
}
