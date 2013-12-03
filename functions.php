<?php
/**
 * @param string $class_name
 */
function et_require($class_name){
	static $required_et_classes;
	if(isset($required_et_classes[$class_name])){
		return;
	}
	$required_et_classes[$class_name] = true;
	$file_path = __DIR__ . "/library/Et/" . str_replace(array("_", "\\"), "/", $class_name) . ".php";
	/** @noinspection PhpIncludeInspection */
	require_once($file_path);
}