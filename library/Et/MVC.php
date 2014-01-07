<?php
namespace Et;
class MVC {

	/**
	 * @var \Et\MVC_Layout
	 */
	protected static $current_layout;

	/**
	 * @param string $module_ID
	 * @return MVC_Modules_Module
	 */
	public static function getModuleInstance($module_ID){
		return MVC_Modules::getModuleInstance($module_ID);
	}



}