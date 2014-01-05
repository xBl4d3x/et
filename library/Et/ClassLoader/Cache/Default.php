<?php
namespace Et;
require_once "Abstract.php";

class ClassLoader_Cache_Default extends ClassLoader_Cache_Abstract {

	/**
	 * @return string
	 */
	protected function getFilePath(){
		return ET_TEMPORARY_DATA_PATH . "class_loader_paths.json";
	}

	/**
	 * @return bool
	 */
	function loadsPaths() {
		$file = $this->getFilePath();
		if(!file_exists($file)){
			return false;
		}

		$data = @file_get_contents($file);
		if(!$data){
			return false;
		}

		$paths = @json_decode($data, true);
		if(!is_array($paths)){
			return false;
		}

		$this->cached_paths = $paths;
		$this->changed = false;

		return true;
	}

	/**
	 * @return bool
	 */
	function storePaths() {

		$file = $this->getFilePath();
		$data = json_encode($this->cached_paths, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

		if(!@file_put_contents($file, $data)){
			return false;
		}

		@chmod($file, ET_DEFAULT_FILES_CHMOD);
		if(ET_DEFAULT_CHOWN_GROUP){
			@chgrp($file, ET_DEFAULT_CHOWN_GROUP);
		}

		if(ET_DEFAULT_CHOWN_USER){
			@chown($file, ET_DEFAULT_CHOWN_USER);
		}

		$this->changed = false;

		return true;
	}

	/**
	 * @return bool
	 */
	function clearPaths() {
		$this->cached_paths = array();
		return $this->storePaths();
	}
}