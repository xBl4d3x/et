<?php
namespace Et;
class DB extends Object {

	const FETCH_VALUES = "values";
	const FETCH_ASSOCIATIVE = "associative";

	/**
	 * @var DB_Config
	 */
	protected static $config;

	/**
	 * @var DB_Adapter_Abstract[]
	 */
	protected static $connections = array();

	/**
	 * @var string
	 */
	protected static $default_connection_name;

	/**
	 * @param DB_Config $config
	 */
	public static function setConfig(DB_Config $config) {
		self::$default_connection_name = null;
		self::$connections = array();
		self::$config = $config;
	}

	/**
	 * @return DB_Config
	 */
	public static function getConfig() {
		if(!self::$config){
			self::$config = DB_Config::getFromEnvironmentConfig();
		}
		return self::$config;
	}

	/**
	 * @return string
	 */
	public static function getDefaultConnectionName(){
		if(!self::$default_connection_name){
			self::$default_connection_name = self::getConfig()->getDefaultConnectionName();
		}
		return self::$default_connection_name;
	}

	/**
	 * @param null|string $connection_name [optional] NULL = Default adapter name
	 *
	 * @return DB_Adapter_Abstract
	 * @throws DB_Exception
	 */
	public static function get($connection_name = null){
		if(!$connection_name){
			$connection_name = self::getDefaultConnectionName();
		}

		if(isset(self::$connections[$connection_name])){
			return self::$connections[$connection_name];
		}

		try {
			$config = self::getConfig()->getConnectionConfig($connection_name);
			$adapter_class = $config->getAdapterClassName();

			self::$connections[$connection_name] = new $adapter_class($config);

		} catch(Exception $e){
			throw new DB_Exception(
				"Failed to get DB connection '{$connection_name}' - {$e->getMessage()}",
				DB_Exception::CODE_INVALID_CONNECTION_NAME,
				null,
				$e
			);
		}

		if(Debug_Profiler::getProfilingEnabled() && $config->getAllowProfiling()){
			$profiler = new DB_Profiler("DB connection '{$connection_name}'");
			self::$connections[$connection_name]->setProfiler($profiler);
			Debug_Profiler::addProfiler($profiler);
		}

		return self::$connections[$connection_name];
	}

	/**
	 * @param string $expression
	 * @return DB_Expression
	 */
	public static function expression($expression){
		return new DB_Expression($expression);
	}

	/**
	 * @param string $column_name
	 * @throws DB_Exception
	 */
	public static function checkColumnName($column_name){
		if(!preg_match('~^\w+(?:\.\w+)?$~', (string)$column_name)){
			throw new DB_Exception(
				"Invalid column name format",
				DB_Exception::CODE_INVALID_COLUMN_NAME
			);
		}
	}

	/**
	 * @param string $table_name
	 * @throws DB_Exception
	 */
	public static function checkTableName($table_name){
		if(!preg_match('~^\w+$~', (string)$table_name)){
			throw new DB_Exception(
				"Invalid table name format",
				DB_Exception::CODE_INVALID_TABLE_NAME
			);
		}
	}
}