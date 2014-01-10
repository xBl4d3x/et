<?php
namespace Et;
class MVC_Modules_Loader extends Loader_Abstract {

	/**
	 * @var string
	 */
	protected $loader_name = "modules_loader";

	/**
	 * @param string $class_name
	 *
	 * @return string|bool
	 */
	public function getClassPath($class_name) {
		if(!preg_match('~^EtM\\\(\w+)\\\(\w+)$~', $class_name, $m)){
			return false;
		}
		list(, $module_name, $class_name) = $m;
		return ET_MODULES_PATH . "{$module_name}/" . str_replace("_", "/", $class_name) . ".php";
	}
}