<?php
namespace Et;
abstract class Loader_Abstract {

	/**
	 * @var string
	 */
	protected $loader_name;

	/**
	 * Load class
	 *
	 * @param string $class_name
	 * @return bool|string Class path
	 */
	public function loadClass($class_name){
		$path = $this->getClassPath($class_name);

		if(!$path || !file_exists($path)){
			return false;
		}

		/** @noinspection PhpIncludeInspection */
		require_once($path);

		$exists = class_exists($class_name, false) ||
		       interface_exists($class_name, false) ||
		       trait_exists($class_name, false);

		if(!$exists){
			return false;
		}

		return $path;
	}

	/**
	 * @return string
	 * @throws Loader_Exception
	 */
	function getLoaderName(){
		if(!$this->loader_name){
			et_require("Loader_Exception");
			throw new Loader_Exception(
				"Missing loader name for class " . get_class($this),
				Loader_Exception::CODE_MISSING_LOADER_NAME
			);
		}
		return $this->loader_name;
	}

	/**
	 * @param string $class_name
	 *
	 * @return string|bool
	 */
	abstract public function getClassPath($class_name);

}