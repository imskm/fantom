<?php

namespace Fantom;

/**
 * Session class : Handles session
 */
class Session
{
	protected static $flash_msg_key = "__FLASH_MSG__";

	public static function hasFlash($key)
	{
		return isset($_SESSION[self::$flash_msg_key][$key]);
	}

	public static function flash($key, $message = "")
	{
		// If key is not valid then throw excpetion
		if (!static::isKeyValid($key)) {
			throw new \Exception("Invalid flash key given");
		}

		// If message given then request is for setting a flash message
		if ($message) {
			$_SESSION[self::$flash_msg_key][$key] = $message;
			return;
		}
		if( ! array_key_exists($key, $_SESSION[self::$flash_msg_key]) ) {
			throw new \Exception("Flash Key : $key not found");
		}

		$message = $_SESSION[self::$flash_msg_key][$key];
		unset($_SESSION[self::$flash_msg_key][$key]);
		return $message;
	}

	private static function isKeyValid($key)
	{
		if (empty($key) || is_null($key)) {
			return false;
		}

		return true;
	}

	public static function set($key, $value)
	{
		if (!static::isKeyValid($key)) {
			throw new \Exception("Invalid flash key given");
		}
		$_SESSION[$key] = $value;
	}

	public static function get($key)
	{
		return array_key_exists($key, $_SESSION) ? $_SESSION[$key] : null;
	}

	public static function delete($key)
	{
		if(array_key_exists($key, $_SESSION)) {
			unset($_SESSION[$key]);
		}
	}

	public static function destroy()
	{
		session_destroy();
	}

	public static function exist($key)
	{
		return array_key_exists($key, $_SESSION);
	}
}
