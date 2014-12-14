<?php

require_once 'ServiceConfig.class.php';
require_once 'ServiceLogger.class.php';
require_once 'session.helper.php';

$lifetime = ServiceConfig::get('runtime', 'session.lifetime');
ini_set('session.auto_start', 0);
ini_set('session.gc_maxlifetime', $lifetime);
ini_set('session.cookie_lifetime', $lifetime * 10);
ini_set('session.gc_divisor', 100);
session_set_save_handler(
		'session_handler_open',
		'session_handler_close',
		'session_handler_read',
		'session_handler_write',
		'session_handler_destroy',
		'session_handler_cleanup');
if (DEBUG) ServiceLogger::debug('[startup] session');
