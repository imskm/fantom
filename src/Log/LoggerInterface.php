<?php

namespace Fantom\Log;

/**
 * Logger Interface
 * 
 *       Numerical         Severity
 *          Code
 *           0       Emergency: system is unusable
 *           1       Alert: action must be taken immediately
 *           2       Critical: critical conditions
 *           3       Error: error conditions
 *           4       Warning: warning conditions
 *           5       Notice: normal but significant condition
 *           6       Informational: informational messages
 *           7       Debug: debug-level messages
 *
 * RFC: https://tools.ietf.org/html/rfc5424
 */
interface LoggerInterface
{
	public function emergency($message);
	public function alert($message);
	public function critical($message);
	public function error($message);
	public function warning($message);
	public function notice($message);
	public function info($message);
	public function debug($message);
}
