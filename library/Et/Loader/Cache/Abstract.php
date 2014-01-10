<?php
namespace Et;
abstract class Loader_Cache_Abstract {

	/**
	 * @var array
	 */
	protected $cached_paths = array();

	/**
	 * @var bool
	 */
	protected $changed = false;

	/**
	 * @return bool
	 */
	function hasChanged(){
		return $this->changed;
	}

	/**
	 * @param string $class_name
	 * @return bool|string
	 */
	function getClassPath($class_name){
		return isset($this->cached_paths[$class_name])
				? $this->cached_paths[$class_name]
				: false;
	}

	/**
	 * @param string $class_name
	 * @param string $path
	 */
	function setClassPath($class_name, $path){
		$this->cached_paths[$class_name] = $path;
		$this->changed = true;
	}

	/**
	 * @return bool
	 */
	abstract function loadsPaths();

	/**
	 * @return bool
	 */
	abstract function storePaths();

	/**
	 * @return bool
	 */
	abstract function clearPaths();

}