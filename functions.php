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

/**
 * @param null|string $path [optional]
 * @param null|mixed $default_value [optional]
 * @return Http_Request_Data_GET|mixed
 */
function GET($path = null, $default_value = null){
	return Et\Http_Request::GET($path, $default_value);
}

/**
 * @param null|string $path [optional]
 * @param null|mixed $default_value [optional]
 * @return Http_Request_Data_POST|mixed
 */
function POST($path = null, $default_value = null){
	return Et\Http_Request::POST($path, $default_value);
}

/**
 * @param null|string $path [optional]
 * @param null|mixed $default_value [optional]
 * @return Http_Request_Data_SERVER|mixed
 */
function SERVER($path = null, $default_value = null){
	return Et\Http_Request::SERVER($path, $default_value);
}