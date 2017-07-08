<?php
// Utility methods for the API
error_reporting(E_ALL);
require(__DIR__.'/../vendor/autoload.php');

use Analog\Analog;

// Convert all PHP errors to exceptions
set_error_handler(function($errno, $errstr, $errfile, $errline) {
	throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
});
set_exception_handler(function($exception) {
	Analog::warning($exception);
	$message = $exception->getMessage();
	if ($exception instanceof \GuzzleHttp\Exception\ClientException) {
		$message .= "\n\nResponse:\n".$exception->getResponse()->getBody();
	}
	ApiResponse::error('500', $message);
});

// Set log file name based on name of script
Analog::handler(Config::LOG_PATH.basename($_SERVER['SCRIPT_NAME'], '.php').'.log');
