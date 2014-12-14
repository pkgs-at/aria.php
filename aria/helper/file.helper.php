<?php

/**
 * ファイル操作関連ユーティリティ関数定義ファイル.
 * 
 * @package aria_helper
 * @author <sotaro.suzuki@architector.jp>
 * @copyright Architector Inc. All rights reserved.
 */

/**
 * ファイルの内容を一時ファイルを利用してアトミックに書き換える.
 * 
 * 本関数はWIN32環境では疑似動作を行う(非アトミック).
 * 
 * @param string $filename 対象のファイルパス.
 * @param string $data 書き換え内容.
 * @return int 書き込まれたバイト数、失敗した場合はFALSE.
 */
function file_replace_contents($filename, $data) {
	if (PHP_OS == 'WIN32' || PHP_OS == 'WINNT') {
		return file_put_contents($filename, $data);
	}
	else {
		$tmpname = tempnam(dirname(realpath($filename)), 'tmp_');
		$result = file_put_contents($tmpname, $data);
		if ($result === FALSE) return FALSE;
		if (!rename($tmpname, $filename)) {
			unlink($tmpname);
			return FALSE;
		}
		return $result;
	}
}

if(!function_exists('parse_ini_string')) :
/**
 * ini形式の設定文字列をパーズする.
 * 
 * 本メソッドは、PHP5.3移行は標準ライブラリがサポート.
 * 
 * @param string $ini 対象文字列.
 * @param boolean $process_sections セクションを使用する場合はTRUE.
 * @param integer $scanner_mode パーズオプション.
 * @return array 設定の連想配列.失敗した場合はFALSEを返す.
 */
function parse_ini_string($ini, $process_sections = FALSE, $scanner_mode = NULL) {
	$tempname = tempnam(sys_get_temp_dir(), 'ini');
	$fp = fopen($tempname, 'w');
	fwrite($fp, $ini);
	$ini = parse_ini_file($tempname, !empty($process_sections));
	fclose($fp);
	@unlink($tempname);
	return $ini;
}
endif;

