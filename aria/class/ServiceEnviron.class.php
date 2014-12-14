<?php

/**
 * ランタイム環境クラス.
 *
 * @package Aria
 * @author <sotaro.suzuki@architector.jp>
 * @copyright Architector Inc. All rights reserved.
 */
class RuntimeEnviron {

	private $rootPath;

	private $configRootPath;

	public function __construct($rootPath) {
		$this->rootPath = $rootPath;
	}

	/**
	 * @return string  ルートパスを取得する.
	 */
	public function getRootPath() {
		return $this->rootPath;
	}

	/**
	 * @return string 設定ルートパスを取得する.
	 */
	public function getConfigRootPath() {
		return $this->configRootPath;
	}

	/**
	 * @param string $configRootPath 設定ルートパスを設定する.
	 */
	public function setConfigRootPath($configRootPath) {
		$this->configRootPath = $configRootPath;
	}

	/**
	 * 相対パスを絶対パスに変換する.
	 * 
	 * @param string $path 相対パス.
	 */
	public function getPath($path) {
		if (strlen($path) > 0 && $path[0] != '/') $path = '/' . $path;
		return $this->getRootPath() . strtr($path, '/', DIRECTORY_SEPARATOR);
	}

}

/**
 * パッケージ環境クラス.
 *
 * @package Aria
 * @author <sotaro.suzuki@architector.jp>
 * @copyright Architector Inc. All rights reserved.
 */
class PackageEnviron {

	private $rootPath;

	private $configRootPath;

	private $serviceLogFilePath;

	public function __construct($rootPath) {
		$this->rootPath = $rootPath;
	}

	/**
	 * @return string  ルートパスを取得する.
	 */
	public function getRootPath() {
		return $this->rootPath;
	}

	/**
	 * @return string 設定ルートパスを取得する.
	 */
	public function getConfigRootPath() {
		return $this->configRootPath;
	}

	/**
	 * @param string $configRootPath 設定ルートパスを設定する.
	 */
	public function setConfigRootPath($configRootPath) {
		$this->configRootPath = $configRootPath;
	}

	/**
	 * @return string サービスログファイルパスを取得する.
	 */
	public function getServiceLogFilePath() {
		return $this->serviceLogFilePath;
	}

	/**
	 * @param string $serviceLogFilePath サービスログファイルパスを設定する.
	 */
	public function setServiceLogFilePath($serviceLogFilePath) {
		$this->serviceLogFilePath = $serviceLogFilePath;
	}

	/**
	 * 相対パスを絶対パスに変換する.
	 * 
	 * @param string $path 相対パス.
	 */
	public function getPath($path) {
		if (strlen($path) > 0 && $path[0] != '/') $path = '/' . $path;
		return $this->getRootPath() . strtr($path, '/', DIRECTORY_SEPARATOR);
	}

}

/**
 * サービス環境クラス.
 *
 * @package Aria
 * @author <sotaro.suzuki@architector.jp>
 * @copyright Architector Inc. All rights reserved.
 */
class ServiceEnviron {

	private $runtimeEnviron;

	private $packageEnviron;

	/**
	 * シングルトンインスタンス.
	 */
	protected static $instance = NULL;

	/**
	 * デフォルトコンストラクタ.
	 */
	protected function __construct() {
	}

	/**
	 * ランタイム環境を設定する.
	 * 
	 * @param RuntimeEnviron $runtimeEnviron ランタイム環境.
	 */
	public function setRuntimeEnviron(RuntimeEnviron $runtimeEnviron) {
		$this->runtimeEnviron = $runtimeEnviron;
	}

	/**
	 * パッケージ環境を設定する.
	 * 
	 * @param PackageEnviron $packageEnviron パッケージ環境.
	 */
	public function setPackageEnviron(PackageEnviron $packageEnviron) {
		$this->packageEnviron = $packageEnviron;
	}

	/**
	 * ランタイムルートパスを取得する.
	 * 
	 * @return string ランタイムルートパス.
	 */
	public function getRuntimeRootPath() {
		return $this->runtimeEnviron->getRootPath();
	}

	/**
	 * ランタイム設定ルートパスを取得する.
	 * 
	 * @return string ランタイム設定ルートパス.
	 */
	public function getRuntimeConfigRootPath() {
		return $this->runtimeEnviron->getConfigRootPath();
	}

	/**
	 * ランタイム内の相対パスを絶対パスに変換する.
	 * 
	 * @param string $path 相対パス.
	 */
	public function getRuntimePath($path) {
		if (strlen($path) > 0 && $path[0] != '/') $path = '/' . $path;
		return $this->getRuntimeRootPath() . strtr($path, '/', DIRECTORY_SEPARATOR);
	}

	/**
	 * ランタイム設定ファイルパスに変換する.
	 * 
	 * @param string $path 絶対パス.
	 */
	public function getRuntimeConfigPath($path) {
		if (strlen($path) > 0 && $path[0] != '/') $path = '/' . $path;
		return $this->getRuntimeConfigRootPath() . strtr($path, '/', DIRECTORY_SEPARATOR);
	}

	/**
	 * パッケージルートパスを取得する.
	 * 
	 * @return string パッケージルートパス.
	 */
	public function getPackageRootPath() {
		return $this->packageEnviron->getRootPath();
	}

	/**
	 * パッケージ設定ルートパスを取得する.
	 * 
	 * @return string パッケージ設定ルートパス.
	 */
	public function getPackageConfigRootPath() {
		return $this->packageEnviron->getConfigRootPath();
	}

	/**
	 * サービスログファイルパスを取得する.
	 * 
	 * @return string サービスログファイルパス.
	 */
	public function getServiceLogFilePath() {
		return $this->packageEnviron->getServiceLogFilePath();
	}

	/**
	 * パッケージ内の相対パスを絶対パスに変換する.
	 * 
	 * @param string $path 相対パス.
	 */
	public function getPackagePath($path) {
		if (strlen($path) > 0 && $path[0] != '/') $path = '/' . $path;
		return $this->getPackageRootPath() . strtr($path, '/', DIRECTORY_SEPARATOR);
	}

	/**
	 * パッケージ設定ファイルパスに変換する.
	 * 
	 * @param string $path 絶対パス.
	 */
	public function getPackageConfigPath($path) {
		if (strlen($path) > 0 && $path[0] != '/') $path = '/' . $path;
		return $this->getPackageConfigRootPath() . strtr($path, '/', DIRECTORY_SEPARATOR);
	}

	/**
	 *  シングルトンインスタンスを返す.
	 *
	 *  @return ServiceEnviron シングルトンインスタンス.
	 */
	public static function get() {
		if (!self::$instance) self::$instance = new ServiceEnviron();
		return self::$instance;
	}

}

