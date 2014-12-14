<?php

require_once 'ServiceConfig.class.php';

/**
 *  URL関連ユーティリティ関数定義ファイル.
 *
 *  @package util
 *  @author <sotaro.suzuki@architector.jp>
 *  @copyright Architector Inc. All rights reserved.
 */

/**
 *  リクエストがHTTPSを使用しているか返す.
 *
 *  @return boolean HTTPSが使用されている場合はTRUE.
 */
function is_secure() {
	static $is_secure = NULL;

	if ($is_secure === NULL) {
		$is_secure = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
	}
	return $is_secure;
}

/**
 *  URL(パス)を絶対URLに変換して返す.
 *
 *  @param string $path URL(パス).
 *  @param boolean $is_secure HTTPSのURLを出力する場合はTRUE.NULLの場合はリクエストと同じ.
 *  @return string 絶対URL.
 */
function absurl($path, $is_secure = NULL) {
	if (preg_match('#https?://#i', $path)) return $path;
	if ($is_secure === NULL) $is_secure = is_secure();
	return ServiceConfig::get('url', $is_secure ? 'root.https' : 'root.http') . $path;
}

/**
 *  URL(パス)にリダイレクトする.
 *
 *  @param string $path URL(パス).
 *  @param boolean $is_secure HTTPSのリダイレクトを出力する場合はTRUE.NULLの場合はリクエストと同じ.
 */
function redirect_path($path, $is_secure = NULL) {
	header('Location: ' . absurl($path, $is_secure));
}
