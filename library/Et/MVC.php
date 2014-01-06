<?php
namespace Et;
class MVC {

	/**
	 * @param string $module_ID
	 * @return MVC_Modules_Module
	 */
	public static function getModuleInstance($module_ID){
		return MVC_Modules::getModuleInstance($module_ID);
	}


}