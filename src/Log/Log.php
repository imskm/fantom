<?php

namespace Fantom\Log;

use Fantom\Log\Logger;

/**
 * Log Wrapper class of Logger
 */
class Log
{
	const STORAGE_PATH = ROOT . "/storage/logs/app.log";

	/**
	 * @var $logger Fantom\Log\Logger
	 */
	protected static $logger = null;

	public static function getLoggerInstance()
	{
		if (is_null(self::$logger)) {
			self::$logger = new Logger(self::STORAGE_PATH);
		}

		return self::$logger;
	}

	public static function __callStatic($name, $args)
	{
		// If method $name is not callable then throw Exception
		if (!is_callable([self::getLoggerInstance(), $name])) {
			throw new \Exception(
				"Method $name is not callable from outside of object "
				. get_class(self::getLoggerInstance()));
		}

		// If $args[0] (i.e. message) is not string then throw Exception
		if (!is_string($args[0])) {
			throw new \Exception("Argument of method $name must be string");
		}

		return call_user_func_array([self::getLoggerInstance(), $name], $args);
	}
}
