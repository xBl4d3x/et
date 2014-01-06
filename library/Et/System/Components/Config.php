<?php
namespace Et;
class System_Components_Config extends Config {

	/**
	 * @var string
	 */
	protected static $_system_config_section = "system|components";

	/**
	 * Definition of config properties
	 *
	 * @var array
	 */
	protected static $_definition = array(
		"backend_config" => [
			self::DEF_TYPE => self::TYPE_CONFIG,
			self::DEF_CONFIG_CLASS => "Et\\System_Components_Backend_Config_Abstract",
			self::DEF_CONFIG_CLASS_TEMPLATE => "Et\\System_Components_Backend_{TYPE}_Config"
		]
	);

	/**
	 * @var System_Components_Backend_Config_Abstract
	 */
	protected $backend_config = array(
		self::CONFIG_TYPE_KEY => "Default"
	);


	/**
	 * @return System_Components_Backend_Config_Abstract
	 */
	public function getBackendConfig() {
		return $this->backend_config;
	}
}