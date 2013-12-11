<?php
namespace Et;
class System_Components_Config extends Config {

	/**
	 * @var string
	 */
	protected static $_environment_config_section = "/system/components";

	/**
	 * @var System_Components_Backend_Config_Abstract
	 */
	protected $backend_config = array(
		"backend_type" => "Default"
	);
	protected static $__backend_config__definition = array(
		self::DEF_TYPE => self::TYPE_CONFIG,
		self::DEF_NAME => "Backend configuration",
		self::DEF_CONFIG_CLASS => 'Et\System_Components_Backend_Config_Abstract'
	);

	/**
	 * @return System_Components_Backend_Config_Abstract
	 */
	public function getBackendConfig() {
		return $this->backend_config;
	}

	/**
	 * @return System_Components_Backend_Abstract
	 */
	public function getBackend(){
		return $this->getBackendConfig()->getBackendInstance();
	}

}