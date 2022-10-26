<?php

namespace Fantom;

use Fantom\View;

/**
 * Error and Exception handler class
 */
class Error
{

	/**
	 * Error handler. Convert all Errors to Excptions by throwing an ErrorException
	 *
	 * @param int $level   Error level
	 * @param string $messsage   Error message
	 * @param string $file   Filename the error was raised in
	 * @param int $line   Line number in the file
	 *
	 * @return void
	 */
	public static function errorHandler($level, $message, $file, $line)
	{
		if((int) error_reporting() !== 0)	// to keep the @ operator working
		{
			throw new \ErrorException($message, 0, $level, $file, $line);
		}
	}

	/**
	 * Exception handler.
	 *
	 * @param Exception $exception    The exception
	 *
	 * @return void
	 */
	public static function exceptionHandler($exception)
 	{
		// Code is 404 (not found) or 500 (general error)
		$code = $exception->getCode();
		if($code != 404) {
			$code = 500;
		}
		http_response_code($code);

		self::outputOrLogError($exception);
 	}

	/**
	 * Shutdown handler
	 * This function is called when php script exits or finishes execution
	 * 
	 * @param void
	 * @return void
	 */
	public static function shutdownHandler()
	{
		$error = error_get_last();

		if ($error)
		{
			$level 		= $error['type'];
			$message 	= $error['message'];
			$file 		= $error['file'];
			$line 		= $error['line'];

			$exception = new \ErrorException($message, 0, $level, $file, $line);
			
			self::outputOrLogError($exception);
		}
	}

	/**
	 * Ouputs Error or Logs error to the file according to config
	 *
	 * @param ErrorException $exception
	 * @return void
	 */
	protected static function outputOrLogError($exception)
	{
		// Code is 404 (not found) or 500 (general error)
		$code = $exception->getCode();
		if ($code != 404) {
			$code = 500;
		}

 		if(\App\Config::SHOW_ERRORS)
		{
			echo "<h1>Fatal Error</h1>";
			echo "<p>Uncaught exception: '" . get_class($exception) . "'</p>";
			echo "<p>Message: '" . $exception->getMessage() . "'</p>";
			echo "<p>Stack trace:<pre>" . $exception->getTraceAsString() . "</pre></p>";
			echo "<p>Thrown in: '" . $exception->getFile() . "' on line <strong>" . $exception->getLine() . "</strong></p>";
		}
		else
		{
			$log = dirname(__DIR__) . '/logs/' . date("Y-m-d") . '.txt';
			ini_set("error_log", $log);

			$message = "Uncaught exception: '" . get_class($exception) . "'";
			$message .= " with message '" . $exception->getMessage() . "'";
			$message .= "\nStack trace: " . $exception->getTraceAsString();
			$message .= "\nThrown in: '" . $exception->getFile() . "' on line " . $exception->getLine();

			error_log($message);
			//echo "<h1>An Error occured</h1>";
			// if($code == 404) {
			// 	echo "<h1>$code Page not found</h1>";
			// } else {
			// 	echo "<h1>$code Internal server error</h1>";
			// }

			$view = new View(VIEW_PATH);
			$view->render("$code.php");
		}
	}

}
