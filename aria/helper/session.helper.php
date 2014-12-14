<?php

require_once 'ServiceConfig.class.php';

/**
 *  セッション関連ユーティリティ関数定義ファイル.
 *
 *  @package Aria
 *  @author <sotaro.suzuki@architector.jp>
 *  @copyright Architector Inc. All rights reserved.
 */

/**
 *  セッションハンドラ(OPEN).
 */
function session_handler_open() {
	global $session_handler_connection;
	global $session_handler_table;

	$session_handler_connection = mysql_pconnect(
			ServiceConfig::get('runtime', 'session.host') . ':' .
			ServiceConfig::get('runtime', 'session.port'),
			ServiceConfig::get('runtime', 'session.username'),
			ServiceConfig::get('runtime', 'session.password'));
	mysql_select_db(
			ServiceConfig::get('runtime', 'session.database'),
			$session_handler_connection);
	$session_handler_table = ServiceConfig::get('runtime', 'session.table');
}

/**
 *  セッションハンドラ(CLOSE).
 */
function session_handler_close() {
	global $session_handler_connection;

	@mysql_close($session_handler_connection);
}

/**
 *  セッションハンドラ(READ).
 *
 *  @param string $key セッションキー.
 */
function session_handler_read($key) {
	global $session_handler_connection;
	global $session_handler_table;

	$key = mysql_real_escape_string($key);
	$result = mysql_query(
			"SELECT ALL `value` FROM `$session_handler_table` WHERE `key` = '$key'",
			$session_handler_connection);
	if (!$result) return '';
	if (mysql_num_rows($result) < 1) return '';
	return mysql_result($result, 0);
}

/**
 *  セッションハンドラ(WRITE).
 *
 *  @param string $key セッションキー.
 *  @param string $key セッションデータ.
 */
function session_handler_write($key, $value) {
	global $session_handler_connection;
	global $session_handler_table;

	$key = mysql_real_escape_string($key);
	$value = mysql_real_escape_string($value);
	return mysql_query(
			"REPLACE INTO `$session_handler_table`(`key`, `value`, `date`) VALUES('$key', '$value', NOW())",
			$session_handler_connection);
}

/**
 *  セッションハンドラ(DESTROY).
 *
 *  @param string $key セッションキー.
 */
function session_handler_destroy($key) {
	global $session_handler_connection;
	global $session_handler_table;

	$key = mysql_real_escape_string($key);
	return mysql_query(
			"DELETE FROM `$session_handler_table` WHERE `key` = '$key'",
			$session_handler_connection);
}

/**
 *  セッションハンドラ(CLEANUP).
 *
 *  @param integer $lifetime セッションライフタイム.
 */
function session_handler_cleanup($lifetime) {
	global $session_handler_connection;
	global $session_handler_table;

	$lifetime = mysql_real_escape_string($lifetime);
	return mysql_query(
			"DELETE FROM `$session_handler_table` WHERE `date` < DATE_SUB(NOW(), INTERVAL $lifetime SECOND)",
			$session_handler_connection);
}
