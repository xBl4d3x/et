<?php
namespace Et;
et_require("Loader_Cache_Abstract");
class Loader_Cache_Default extends Loader_Cache_Abstract {

	/**
	 * @return string
	 */
	protected function getFilePath(){
		return ET_TEMPORARY_DATA_PATH . "class_loader_paths.json";
	}

	/**
	 * @return array
	 */
	function loadsPaths() {
		$file = $this->getFilePath();
		if(!file_exists($file)){
			return array();
		}
		$data = @file_get_contents($file);
		if(!$data){
			return array();
		}
		$paths = @json_decode($data);
		if(!is_array($paths)){
			return array();
		}
		return $paths;
	}

	/**
	 * @param array $paths
	 * @return bool
	 */
	function storePaths(array $paths) {
		$file = $this->getFilePath();
		$data = json_encode($paths, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
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
		return true;
	}

	/**
	 * @return bool
	 */
	function clearPaths() {
		$file = $this->getFilePath();
		if(!file_exists($file)){
			return true;
		}
		return @unlink($file);
	}
}