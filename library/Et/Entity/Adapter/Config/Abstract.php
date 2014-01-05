<?php
namespace Et;
abstract class Entity_Adapter_Config_Abstract extends Config {

	const ADAPTER_CLASS_NAME_TEMPLATE = "Et\\Entity_Adapter_{TYPE}";

	/**
	 * @return string|\Et\DB_Adapter_Abstract
	 */
	function getAdapterClassName(){
		return Factory::getClassName(
			str_replace("{TYPE}", $this->getType(), static::ADAPTER_CLASS_NAME_TEMPLATE),
			"Et\\Entity_Adapter_Abstract"
		);
	}

}