<?php

namespace Fantom\Database;

use App\Config;

/**
* Connector class
* Let's app to connect to DB with appropriate DSN
*/
class Connector
{
	/**
	 * @var resource  $conn db Connection
	 */
	protected static $db = null;

	protected function __construct()
	{

	}

	public static function getConnection()
	{
		if (self::$db === null) {
			try {
				self::$db = new \PDO(
					self::getConnectionString(),
					Config::DB_USER,
					Config::DB_PASSWORD
				);

				// Throw an Exception when Error occurs
				self::$db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
			} catch (\Exception $e) {
				throw new \Exception("ERROR : " . $e->getMessage());
			}
		}

		return self::$db;
	}

	private static function getConnectionString()
	{
		$format = "mysql:host=%s;port=%s;dbname=%s;charset=utf8";

		return sprintf($format, Config::DB_HOST, Config::DB_PORT, Config::DB_NAME);
	}
}