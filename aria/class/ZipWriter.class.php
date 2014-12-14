<?php

/**
 * @internal
 */
class ZipWriter_CountFilter extends php_user_filter {

	private $length;

	#Override
	public function onCreate() {
		$this->length = 0;
		$callback = $this->params;
		if ($callback) call_user_func($callback, $this);
	}

	public function reset() {
		$this->length = 0;
	}

	public function getLength() {
		return $this->length;
	}

	#Override
	public function filter($in, $out, &$consumed, $closing) {
		while ($bucket = stream_bucket_make_writeable($in)) {
			$consumed += $bucket->datalen;
			$this->length += $bucket->datalen;
			stream_bucket_append($out, $bucket);
		}
		return PSFS_PASS_ON;
	}

	#Override
	public function onClose() {
		// do nothing
	}

}
stream_filter_register('zip_writer.counter', 'ZipWriter_CountFilter');

/**
 * @internal
 */
class ZipWriter_CrcComputeFilter extends php_user_filter {

	private $computer;

	#Override
	public function onCreate() {
		$this->computer = hash_init('crc32b');
		$callback = $this->params;
		if ($callback) call_user_func($callback, $this);
	}

	#Override
	public function filter($in, $out, &$consumed, $closing) {
		while ($bucket = stream_bucket_make_writeable($in)) {
			hash_update($this->computer, $bucket->data);
			$consumed += $bucket->datalen;
			stream_bucket_append($out, $bucket);
		}
		return PSFS_PASS_ON;
	}

	#Override
	public function onClose() {
		// do nothing
	}

	public function getValue() {
		return hash_final($this->computer, TRUE);
	}

}
stream_filter_register('zip_writer.crc_computer', 'ZipWriter_CrcComputeFilter');

/**
 *  ZIPストリーム出力クラス.
 *
 *  header('Content-type: application/zip');
 *  header('Content-Disposition: attachment; filename="downloaded.zip"');
 *  $writer = new ZipWriter('php://output', 'wb', FALSE, NULL);
 *  $context = new stream_context_create();
 *  $options = array();
 *  $options['method'] = 'GET';
 *  if (isset($_SERVER['HTTP_COOKIE'])) $options['header'][] = 'Cookie: ' .  $_SERVER['HTTP_COOKIE'];
 *  stream_context_set_option($context, array('http' => $options));
 *  $writer->setEncoding('SJIS-win');
 *  $writer->appendFile('path/to/filename1', 'http://localhost/1', FALSE, $context);
 *  $writer->appendFile('path/to/filename2', 'http://localhost/2', FALSE, $context);
 *  $writer->close();
 *
 *  @package At_Pkgs_Util
 *  @author <sotaro.suzuki@architector.jp>
 *  @copyright Architector Inc. All rights reserved.
 */
class ZipWriter {

	protected $offset;
	protected $files;
	protected $central;
	protected $output;
	protected $compress;
	protected $encoding;
	protected $comment;

	private $inputCounter;
	private $outputCounter;
	private $crcComputer;

	/**
	 * 出力先を指定してZIPストリーム出力を初期化する.
	 * 
	 * @param string $filename ファイル名.
	 * @param string $mode ファイルアクセスモード.
	 * @param boolean $use_include_path インクルードパスをサーチパスとして使用する場合はTRUE.
	 * @param resource $context 出力ストリームコンテキスト.
	 * @throws Exception 出力先ファイルのオープンに失敗した.
	 */
	public function __construct($filename, $mode = 'wb', $use_include_path = FALSE, $context = NULL) {
		$this->offset = 0;
		$this->files = 0;
		$this->central = '';
		if (!$context) $context = stream_context_create();
		$this->output = fopen($filename, $mode, $use_include_path, $context);
		if ($this->output === FALSE) throw new Exception('ZipWriter: failed on open output file: ' . $filename);
		$this->compress = (-1);
		$this->encoding = mb_internal_encoding();
		$this->comment = '';
	}

	/**
	 * 圧縮レベルを-1(デフォルト:6)、0(無圧縮)または1(最速)~9(最小)の値で設定する.
	 * 
	 * @param integer $level 圧縮レベル.
	 */
	public function setCompress($level) {
		$this->compress = $level;
	}

	/**
	 * ZIPファイル内の文字列の出力エンコーディングを指定する.
	 * 
	 * 過去のWindowsとの互換性のために日本語では'SJIS-win'推奨.
	 * 
	 * @param string $encoding エンコーディング.
	 */
	public function setEncoding($encoding) {
		$this->encoding = $encoding;
	}

	/**
	 * ZIPファイルコメントを設定する.
	 * 
	 * @param string $comment コメント文字列.
	 */
	public function setComment($comment) {
		$this->comment = $comment;
	}

	/**
	 * @internal
	 */
	public function _setInputCounter($filter) {
		$this->inputCounter = $filter;
	}

	/**
	 * @internal
	 */
	public function _setOutputCounter($filter) {
		$this->outputCounter = $filter;
	}

	/**
	 * @internal
	 */
	public function _setCrcComputer($filter) {
		$this->crcComputer = $filter;
	}

	protected function sizeof($value) {
		return strlen(bin2hex($value)) >> 1;
	}

	protected function date($time = NULL) {
		if ($time === NULL) $time = time();
		$date = getdate($time);
		$time = 0;
		$time |= ($date['year'] - 1980) & 0x7F;
		$time <<= 4;
		$time |= $date['mon'] & 0x0F;
		$time <<= 5;
		$time |= $date['mday'] & 0x1F;
		$time <<= 5;
		$time |= $date['hours'] & 0x1F;
		$time <<= 6;
		$time |= $date['minutes'] & 0x3F;
		$time <<= 5;
		$time |= ($date['seconds'] >> 1) & 0x1F;
		return $time;
	}

	protected function write($data) {
		if (func_num_args() > 1) {
			$arguments = func_get_args();
			$data = call_user_func_array('pack', $arguments);
		}
		$this->offset += fwrite($this->output, $data);
	}

	/**
	 * 入力ストリームを指定してファイルを追加する.
	 * 
	 * このメソッドは入力ストリームを自動的に閉じる.
	 * 
	 * @param string $name アーカイブ内のファイル名(相対パス).
	 * @param resource $stream 入力ストリーム.
	 */
	public function appendStream($name, $stream) {
		$offset = $this->offset;
		if (stream_is_local($stream)) {
			$status = fstat($stream);
			$date = $this->date($status['mtime']);
		}
		else {
			$date = $this->date();
		}
		$name = mb_convert_encoding($name, $this->encoding, mb_internal_encoding());
		$this->write(
				'VvvvVVVVvv',
				0x04034B50,
				20,
				0x0008,
				0x0008,
				$date,
				0x00000000,
				0x00000000,
				0x00000000,
				$this->sizeof($name),
				0x0000);
		$this->write($name);
		stream_filter_append($stream, 'zip_writer.crc_computer', STREAM_FILTER_READ, array($this, '_setCrcComputer'));
		stream_filter_append($stream, 'zip_writer.counter', STREAM_FILTER_READ, array($this, '_setInputCounter'));
		stream_filter_append($stream, 'zlib.deflate', STREAM_FILTER_READ, $this->compress);
		stream_filter_append($stream, 'zip_writer.counter', STREAM_FILTER_READ, array($this, '_setOutputCounter'));
		stream_copy_to_stream($stream, $this->output);
		fclose($stream);
		$checksum = strrev($this->crcComputer->getValue());
		$rawlength = $this->inputCounter->getLength();
		$length = $this->outputCounter->getLength();
		$this->offset += $length;
		$this->write(
				'Va4VV',
				0x08074B50,
				$checksum,
				$length,
				$rawlength);
		$this->central .= pack(
				'VvvvvVa4VVvvvvvVV',
				0x02014B50,
				20,
				20,
				0x0008,
				0x0008,
				$date,
				$checksum,
				$length,
				$rawlength,
				$this->sizeof($name),
				0x0000,
				0x0000,
				0x0000,
				0x0000,
				0x00000000,
				$offset);
		$this->files ++;
		$this->central .= $name;
	}

	/**
	 * ファイルを追加する.
	 * 
	 * @param string $name アーカイブ内のファイル名(相対パス).
	 * @param string $filename ファイル名.
	 * @param boolean $use_include_path インクルードパスをサーチパスとして使用する場合はTRUE.
	 * @param resource $context 入力ストリームコンテキスト.
	 * @throws Exception 入力元ファイルのオープンに失敗した.
	 */
	public function appendFile($name, $filename, $use_include_path = FALSE, $context = NULL) {
		if (!$context) $context = stream_context_create();
		$input = fopen($filename, 'rb', $use_include_path, $context);
		if ($input === FALSE) throw new Exception('ZipWriter: failed on open input file: ' . $filename);
		$this->appendStream($name, $input);
	}

	/**
	 * ZIPセントラルヘッダを出力し、ZIP出力ストリームを閉じる.
	 */
	public function close() {
		$offset = $this->offset;
		$comment = mb_convert_encoding($this->comment, $this->encoding, mb_internal_encoding());
		$this->write($this->central);
		$this->write(
				'VvvvvVVv',
				0x06054B50,
				0x0000,
				0x0000,
				$this->files,
				$this->files,
				$this->sizeof($this->central),
				$offset,
				$this->sizeof($comment));
		$this->write($comment);
		fclose($this->output);
	}

}

if (FALSE) :
mb_internal_encoding('UTF-8');
$instance = new ZipWriter('php://output', 'wb');
$instance->setEncoding('SJIS-win');
$instance->setComment('これはテストアーカイブです。');
$instance->appendFile('test/Zipper.class.php', __FILE__);
$instance->appendFile('Zipper.class.php', __FILE__);
$instance->appendFile('test/テストZipper.class.php', __FILE__);
$instance->close();
endif;

