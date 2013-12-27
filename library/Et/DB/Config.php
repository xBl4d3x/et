<?php
namespace Et;
class DB_Config extends Config {

	const ERR_INVALID_CONNECTION_NAME = "invalid_connection_name";

	/**
	 * @var string
	 */
	protected static $_system_config_section = "database";

	/**
	 * @var array
	 */
	protected static $_error_codes_list = array(
		self::ERR_INVALID_CONNECTION_NAME => "Invalid connection name '{CONNECTION_NAME}'"
	);

	/**
	 * Definition of config properties
	 *
	 * @var array
	 */
	protected static $_definition = array(
		"default_connection_name" => [
			self::DEF_FORMAT => array(__CLASS__, "validateConnectionName")
		],
		"connections_configs" => [
			self::DEF_TYPE => self::TYPE_CONFIGS_LIST,
			self::DEF_CONFIG_CLASS => "Et\\DB_Adapter_Config_Abstract",
			self::DEF_CONFIG_CLASS_TEMPLATE => "Et\\DB_Adapter_{TYPE}_Config"
		]
	);

	/**
	 * @var string
	 */
	protected $default_connection_name = "default";

	/**
	 * @var DB_Adapter_Config_Abstract[]
	 */
	protected $connections_configs = array();


	/**
	 * @param $value
	 * @param $property_name
	 * @param array $definition
	 * @param DB_Config $config
	 * @param null $error_code
	 * @param null $error_message
	 * @return bool
	 */
	function validateConnectionName($value, $property_name, array $definition, DB_Config $config, &$error_code = null, &$error_message = null){
		if($config->getConnectionExists($value)){
			return true;
		}
		$error_code = self::ERR_INVALID_CONNECTION_NAME;
		$error_message = str_replace("{CONNECTION_NAME}", $value, static::$_error_codes_list[$error_code]);
		return false;
	}


	/**
	 * @return DB_Adapter_Config_Abstract[]
	 */
	public function getConnectionsConfigs() {
		return $this->connections_configs;
	}

	/**
	 * @return string
	 */
	public function getDefaultConnectionName() {
		return $this->default_connection_name;
	}

	/**
	 * @param string $connection_name
	 * @return bool
	 */
	public function getConnectionExists($connection_name){
		return isset($this->connections_configs[$connection_name]);
	}

	/**
	 * @param string $connection_name
	 * @return DB_Adapter_Config_Abstract
	 * @throws DB_Exception
	 */
	public function getConnectionConfig($connection_name){
		if(!$this->getConnectionExists($connection_name)){
			throw new DB_Exception(
				"DB connection '{$connection_name}' configuration not found",
				DB_Exception::CODE_INVALID_CONNECTION_NAME
			);
		}
		return $this->connections_configs[$connection_name];
	}



}