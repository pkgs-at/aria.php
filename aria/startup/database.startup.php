<?php

require_once 'ServiceEnviron.class.php';
require_once 'ServiceConfig.class.php';
require_once 'ServiceLogger.class.php';
require_once 'Propel.php';
require_once 'AbstractModel.class.php';
require_once 'AbstractModelQuery.class.php';

$config = array();
$config['datasources']['main']['adapter'] = 'mysql';
$connection = array();
$connection['classname'] = DEBUG ? 'DebugPDO' : 'PropelPDO';
$connection['dsn'] = sprintf('mysql:host=%s;port=%d;dbname=%s',
                             ServiceConfig::get('runtime', 'datasource.master.host'),
                             ServiceConfig::get('runtime', 'datasource.master.port'),
                             ServiceConfig::get('runtime', 'datasource.master.database'));
$connection['user'] =        ServiceConfig::get('runtime', 'datasource.master.username');
$connection['password'] =    ServiceConfig::get('runtime', 'datasource.master.password');
$connection['settings']['charset']['value'] = 'utf8';
$connection['options']['MYSQL_ATTR_INIT_COMMAND']['value'] = 'SET NAMES `utf8` COLLATE `utf8_unicode_ci`';
$config['datasources']['main']['connection'] = $connection;
$connection = array();
$connection['classname'] = DEBUG ? 'DebugPDO' : 'PropelPDO';
$connection['dsn'] = sprintf('mysql:host=%s;port=%d;dbname=%s',
                             ServiceConfig::get('runtime', 'datasource.slave.host'),
                             ServiceConfig::get('runtime', 'datasource.slave.port'),
                             ServiceConfig::get('runtime', 'datasource.slave.database'));
$connection['user'] =        ServiceConfig::get('runtime', 'datasource.slave.username');
$connection['password'] =    ServiceConfig::get('runtime', 'datasource.slave.password');
$connection['settings']['charset']['value'] = 'utf8';
$connection['options']['MYSQL_ATTR_INIT_COMMAND']['value'] = 'SET NAMES `utf8` COLLATE `utf8_unicode_ci`';
$config['datasources']['main']['slaves']['connection'][] = $connection;
$config['datasources']['default'] = 'main';
if (DEBUG) {
	$config['debugpdo']['logging']['details']['method'] = array('enabled' => TRUE);
	$config['debugpdo']['logging']['details']['time'] =   array('enabled' => TRUE, 'precision' => 3);
	$config['debugpdo']['logging']['details']['mem'] =    array('enabled' => TRUE, 'precision' => 1);
}
$config['classmap'] = require ServiceEnviron::get()->getRuntimeConfigPath('propel.classmap.php');
Propel::setLogger(ServiceLogger::factory('propel', ServiceLogger::DEBUG));
Propel::setConfiguration($config);
Propel::initialize();
if (DEBUG) {
	$methods = array(
			'PropelPDO::__construct',       // logs connection opening
			'PropelPDO::__destruct',        // logs connection close
			'PropelPDO::exec',              // logs a query
			'PropelPDO::query',             // logs a query
			'PropelPDO::prepare',           // logs the preparation of a statement
			'PropelPDO::beginTransaction',  // logs a transaction begin
			'PropelPDO::commit',            // logs a transaction commit
			'PropelPDO::rollBack',          // logs a transaction rollBack (watch out for the capital 'B')
			'DebugPDOStatement::execute',   // logs a query from a prepared statement
			'DebugPDOStatement::bindValue'  // logs the value and type for each bind
	);
	$config = Propel::getConfiguration(PropelConfiguration::TYPE_OBJECT);
	$config->setParameter('debugpdo.logging.methods', $methods, FALSE);
}
if (DEBUG) ServiceLogger::debug('[startup] database');
