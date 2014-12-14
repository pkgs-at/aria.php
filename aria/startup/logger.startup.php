<?php

require_once 'ServiceConfig.class.php';
require_once 'ServiceLogger.class.php';

function error_handler($code, $message, $file, $line) {
	static $logger = NULL;

	if ($logger === NULL) {
		$logger = ServiceLogger::factory('php', ServiceLogger::DEBUG);
	}
	switch ($code) {
	case E_WARNING:
	case E_USER_WARNING:
		$priority = PEAR_LOG_WARNING;
		break;
	case E_NOTICE:
	case E_USER_NOTICE:
		$priority = PEAR_LOG_NOTICE;
		break;
	default:
		$priority = PEAR_LOG_ERR;
		break;
	}
	$logger->log($message . ' in ' . $file . ' at line ' . $line, $priority);
	return FALSE;
}
set_error_handler('error_handler', -1);
function shutdown() {
	$error = error_get_last();
	if (empty($error)) return;
	error_handler($error['type'], $error['message'], $error['file'], $error['line']);
}
register_shutdown_function('shutdown');
if (DEBUG) ServiceLogger::debug('[startup] logger');
