<?php
namespace Et;
abstract class DB_Adapter_Config_Abstract extends Config {

	const ADAPTER_CLASS_NAME_TEMPLATE = "Et\\DB_Adapter_{TYPE}";

	/**
	 * @var bool
	 */
	protected $allow_profiling = true;

	/**
	 * @return string|\Et\DB_Adapter_Abstract
	 */
	function getAdapterClassName(){
		return Factory::getClassName(
			str_replace("{TYPE}", $this->getType(), static::ADAPTER_CLASS_NAME_TEMPLATE),
			"Et\\DB_Adapter_Abstract"
		);
	}

	/**
	 * @return boolean
	 */
	public function getAllowProfiling() {
		return $this->allow_profiling;
	}



}