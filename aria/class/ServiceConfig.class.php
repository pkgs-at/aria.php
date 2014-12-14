<?php

require_once 'ServiceEnviron.class.php';

/**
 *  サービス設定クラス.
 *
 *  @package Aria
 *  @author <sotaro.suzuki@architector.jp>
 *  @copyright Architector Inc. All rights reserved.
 */
class ServiceConfig {

	/**
	 *  設定値の連想配列.
	 */
	protected $config;

	/**
	 *  シングルトンインスタンス.
	 */
	protected static $instance = NULL;

	/**
	 *  デフォルトコンストラクタ.
	 */
	protected function __construct() {
		$this->config = parse_ini_file(ServiceEnviron::get()->getRuntimeConfigPath('service.ini'), TRUE);
	}

	/**
	 *  シングルトンインスタンスを返す.
	 *
	 *  @return ServerConfig シングルトンインスタンス.
	 */
	protected static function instance() {
		if (!self::$instance) self::$instance = new ServiceConfig();
		return self::$instance;
	}

	/**
	 *  設定を返す.
	 *
	 *  @param string $section セクション名.
	 *  @param string $key キー名.指定しない場合はセクションの設定が連想配列で返される.
	 *  @param mixed $default デフォルト値.
	 *  @return mixed 設定.
	 */
	public static function get($section, $key = NULL, $default = NULL) {
		$config = self::instance();
		$config = $config->config;
		$config = isset($config[$section]) ? $config[$section] : array();
		if (!$key) return $config;
		$config = isset($config[$key]) ? $config[$key] : $default;
		return $config;
	}

}

