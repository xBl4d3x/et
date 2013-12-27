<?php
namespace Et;
et_require("Loader_Abstract");
class Application_Loader extends Loader_Abstract {

	/**
	 * @var string
	 */
	protected $loader_name = "application_loader";

	/**
	 * @param string $class_name
	 *
	 * @return string|bool
	 */
	public function getClassPath($class_name) {
		if(!preg_match('~^EtApp\\\(\w+)\\\(\w+)$~', $class_name, $m)){
			return false;
		}
		list(, $application_ID, $class_name) = $m;
		return ET_APPLICATIONS_PATH . "{$application_ID}/classes/" . str_replace("_", "/", $class_name) . ".php";
	}
}