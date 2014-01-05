<?php
namespace Et;
abstract class ClassLoader_Abstract {

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
	 * @param string $class_name
	 *
	 * @return string|bool
	 */
	abstract public function getClassPath($class_name);

}