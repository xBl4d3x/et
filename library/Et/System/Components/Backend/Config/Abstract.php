<?php
namespace Et;
et_require("Config");
abstract class System_Components_Backend_Config_Abstract extends Object_Adapter_Config {

	/**
	 * @var string
	 */
	protected static $__adapter_class_prefix = 'Et\System_Components_Backend';

	/**
	 * @var string
	 */
	protected static $__adapter_type_options_key = "backend_type";


	/**
	 * @return System_Components_Backend_Abstract
	 */
	public function getBackendInstance(){
		return $this->getAdapter();
	}
}