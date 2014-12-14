<?php

require_once 'Log.php';
require_once 'ServiceEnviron.class.php';
require_once 'ServiceConfig.class.php';

/**
 *  サービスログクラス.
 *
 *  @package Aria
 *  @author <sotaro.suzuki@architector.jp>
 *  @copyright Architector Inc. All rights reserved.
 */
class ServiceLogger {

	const DEBUG = PEAR_LOG_DEBUG;
	const INFORMATION = PEAR_LOG_INFO;
	const WARING = PEAR_LOG_WARNING;
	const ERROR = PEAR_LOG_ERR;
	const FATAL = PEAR_LOG_CRIT;

	/**
	 *  Pear::Logインスタンス.
	 */
	protected $logger;

	/**
	 *  シングルトンインスタンス.
	 */
	protected static $instance = NULL;

	/**
	 *  デフォルトコンストラクタ.
	 */
	protected function __construct() {
		$level = PEAR_LOG_INFO;
		if (ServiceConfig::get('debug', 'enabled')) {
			$level = PEAR_LOG_DEBUG;
		}
		$this->logger = self::factory('service', $level, DEBUG ? 1 : (-1));
	}

	public static function factory($name, $level = PEAR_LOG_INFO, $backtrace = -1) {
		$options = array();
		$options['lineFormat'] = '%1$s %2$-8s [%3$-8s] %4$s';
		if ($backtrace >= 0) {
			$options['lineFormat'] .= "\n" . '# %8$s::%7$s() at %5$s:%6$s';
		}
		$options['timeFormat'] = '%Y-%m-%dT%H:%M:%S';
		$options['eol'] = "\n";
		$logger = Log::factory('file', ServiceEnviron::get()->getServiceLogFilePath(), $name, $options, $level);
		if ($backtrace >= 0) {
			$logger->setBacktraceDepth($backtrace);
		}
		return $logger;
	}

	/**
	 *  シングルトンインスタンスを返す.
	 *
	 *  @return ServerLog シングルトンインスタンス.
	 */
	protected static function instance() {
		if (!self::$instance) self::$instance = new ServiceLogger();
		return self::$instance;
	}

	public static function isEnabled($level) {
		return (Log::MASK($level) & self::instance()->logger->getMask());
	}

	public static function log($level, $args) {
		if (!self::isEnabled($level)) return;
		if (is_array($args)) {
			$format = array_shift($args);
			$message = vsprintf($format, $args);
		}
		elseif (func_num_args() > 2) {
			$args = func_get_args();
			array_shift($args);
			$format = array_shift($args);
			$message = vsprintf($format, $args);
		}
		else {
			$message = $args;
		}
		self::instance()->logger->log($message, $level);
	}

	public static function debug($format) {
		$args = func_get_args();
		self::log(self::DEBUG, $args);
	}

	public static function info($format) {
		$args = func_get_args();
		self::log(self::INFORMATION, $args);
	}

	public static function warn($format) {
		$args = func_get_args();
		self::log(self::WARNING, $args);
	}

	public static function error($format) {
		$args = func_get_args();
		self::log(self::ERROR, $args);
	}

	public static function fatal($format) {
		$args = func_get_args();
		self::log(self::FATAL, $args);
	}

}

