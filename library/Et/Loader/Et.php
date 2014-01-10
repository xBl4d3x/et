<?php
namespace Et;
et_require("Loader_Abstract");
class Loader_Et extends Loader_Abstract {


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
	 * @throws Loader_Exception
	 */
	function getLoaderName() {
		return "et";
	}
}