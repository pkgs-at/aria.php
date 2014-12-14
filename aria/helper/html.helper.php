<?php

require_once 'url.helper.php';

/**
 *  HTML出力関連ユーティリティ関数定義ファイル.
 *
 *  @package util
 *  @author <sotaro.suzuki@architector.jp>
 *  @copyright Architector Inc. All rights reserved.
 */

/**
 *  文字列をHTML出力する.
 *
 *  @param string $string 文字列.
 *  @param boolean $nl2br TRUEの場合改行をHTMLに変換する.
 */
function html($string, $nl2br = FALSE) {
	if ($nl2br) {
		echo nl2br(htmlspecialchars($string));
	}
	else {
		echo htmlspecialchars($string);
	}
}

/**
 *  文字列をHTML出力する.
 *
 *  @param string $string 文字列.
 *  @param boolean $nl2br TRUEの場合改行をHTMLに変換する.
 */
function html_autolink($string, $nl2br = FALSE) {
	if ($nl2br) {
		$string = nl2br(htmlspecialchars($string));
	}
	else {
		$string = htmlspecialchars($string);
	}
	echo preg_replace('#(https?|ftp)(://[[:alnum:]\+\$\;\?\.%,!\\#~*/:@&=_-]+)#i', '<a href="\\1\\2" target="_blank">\\1\\2</a>', $string);
}

/**
 *  日時をHTML出力する.
 *
 *  @param string $value 日時.
 */
function html_datetime($value) {
	if ($value instanceof DateTime) {
		$value = $value->format('Y年n月j日 G:i');
	}
	else if (is_string($value)) {
		$value = date('Y年n月j日 G:i', strtotime($value));
	}
	else {
		$value = date('Y年n月j日 G:i', $value);
	}
	html($value);
}

/**
 *  URL(パス)を絶対URLに変換してHTML出力する.
 *
 *  @param string $path URL(パス).
 *  @param boolean $is_secure HTTPSのURLを出力する場合はTRUE.NULLの場合はリクエストと同じ.
 */
function html_absurl($path, $is_secure = NULL) {
	html(absurl($path, $is_secure));
}
