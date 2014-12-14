<?php

/**
 * 文字列関連ユーティリティ関数定義ファイル.
 * 
 * @package aria_helper
 * @author <sotaro.suzuki@architector.jp>
 * @copyright Architector Inc. All rights reserved.
 */

/**
 * 文字列をキャメルケースに変換する.
 * 
 * @param string $source 文字列.
 * @param string $delimitor 区切り文字.
 * @return string キャメルケースの文字列.
 */
function strtocamel($source, $delimitor = '_') {
	$to = '';
	foreach (explode($delimitor, $source) as $part) $to .= ucfirst($part);
	return $to;
}

/**
 * 文字列が検索文字列で始まるか調べる.
 * 
 * @param string $haystack 文字列.
 * @param string $needle 検索文字列.
 * @return boolean 検索文字で始まるならばTRUE.
 */
function str_startswith($haystack, $needle) {
	return substr($haystack, 0, strlen($needle)) === $needle;
}

/**
 * 大文字小文字を区別せず、文字列が検索文字列で始まるか調べる.
 * 
 * @param string $haystack 文字列.
 * @param string $needle 検索文字列.
 * @return boolean 検索文字で始まるならばTRUE.
 */
function str_istartswith($haystack, $needle) {
	return str_startswith(strtoupper($haystack), strtoupper($needle));
}

/**
 * 文字列が検索文字列で終わるか調べる.
 * 
 * @param string $haystack 文字列.
 * @param string $needle 検索文字列.
 * @return boolean 検索文字で終わるならばTRUE.
 */
function str_endswith($haystack, $needle) {
	return substr($haystack, -strlen($needle)) === $needle;
}

/**
 * 大文字小文字を区別せず、文字列が検索文字列で終わるか調べる.
 * 
 * @param string $haystack 文字列.
 * @param string $needle 検索文字列.
 * @return boolean 検索文字で終わるならばTRUE.
 */
function str_iendswith($haystack, $needle) {
	return str_endswith(strtoupper($haystack), strtoupper($needle));
}

