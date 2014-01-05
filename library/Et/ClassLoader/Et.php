<?php
namespace Et;
et_require("ClassLoader_Abstract");
class ClassLoader_Et extends ClassLoader_Abstract {


	/**
	 * @param string $class_name
	 *
	 * @return string|bool
	 */
	public function getClassPath($class_name) {
		if(substr($class_name, 0, 3) != "Et\\"){
			return false;
		}
		return dirname(__DIR__) . "/" . str_replace("_", "/", substr($class_name, 3)) . ".php";
	}

	/**
	 * @return string
	 * @throws ClassLoader_Exception
	 */
	function getLoaderName() {
		return "et";
	}
}