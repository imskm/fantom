<?php

namespace Fantom\Log;

use Fantom\Log\LoggerInterface;

/**
 * Logger Class
 */
class Logger implements LoggerInterface
{
	/**
	 * @var $storage_path string
	 */
	protected $storage_path;

	public function __construct($storage_path)
	{
		$this->storage_path = $storage_path;
	}

	public function emergency($message)
	{
		$this->writeLog(__FUNCTION__, $message);
	}

	public function alert($message)
	{
		$this->writeLog(__FUNCTION__, $message);
	}

	public function critical($message)
	{
		$this->writeLog(__FUNCTION__, $message);
	}

	public function error($message)
	{
		$this->writeLog(__FUNCTION__, $message);
	}

	public function warning($message)
	{
		$this->writeLog(__FUNCTION__, $message);
	}

	public function notice($message)
	{
		$this->writeLog(__FUNCTION__, $message);
	}

	public function info($message)
	{
		$this->writeLog(__FUNCTION__, $message);
	}

	public function debug($message)
	{
		$this->writeLog(__FUNCTION__, $message);
	}

	protected function writeLog($level, $message)
	{
		$fmt_message = $this->formatMessage($level, $message);

		$fh = $this->getStorageHandler($this->storage_path);
		fwrite($fh, "$fmt_message\n");
		fclose($fh);
	}

	protected function formatMessage($level, $message)
	{
		$caller_stack_index = 4; /* array index # of backtrace result will
									always be the caller stack frame */
		$debug_info 	= debug_backtrace();
		$debug_info 	= $debug_info[$caller_stack_index];
		$severity 		= $level;
		$timestamp 		= date("Y-m-d\TH:i:s");
		$machine 		= "-";
		$app_name 		= "-";
		$file_name 		= $debug_info['file'];
		$line_no 		= $debug_info['line'];
		$proc_id 		= "-";
		$msg_id 		= "-";
		$msg 			= $message;

		$fmt_message = "<$severity> $timestamp $machine $file_name($line_no) $msg";

		return $fmt_message;
	}

	protected function getStorageHandler($storage_path)
	{
		$max_bytes = 1048576; // 1MB = 1048576 bytes
		$fh = $this->openLogFile($storage_path);
		fseek($fh, 0, SEEK_END);

		// If log file size hits 1MB or more then create fresh new log file
		if (ftell($fh) >= $max_bytes) {
			fclose($fh);
			// Rename the old file and create new one
			if (rename($storage_path, "$storage_path_".time()) === false) {
				// Don't know what to do when renaming fails
			}
			$fh = $this->openLogFile($storage_path);
		}

		return $fh;
	}

	protected function openLogFile($storage_path)
	{
		$fh = fopen($storage_path, "a+");
		if ($fh === false) {
			throw new \Exception("Failed to open/create \"$storage_path\" file");
		}

		return $fh;
	}
}
