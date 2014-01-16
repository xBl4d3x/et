<?php
namespace EtTest;
use Et;

require(dirname(__DIR__) . "/environment.php");
require(dirname(__DIR__) . "/init.php");

set_include_path(get_include_path() . PATH_SEPARATOR . "phar://".__DIR__."/phpunit.phar"  );

function et_phpunit_loader($class_name){
	if(substr($class_name, 0, 7) != 'PHPUnit'){
		return;
	}
	$class_name = str_replace(array("_", '\\'), "/", $class_name);
	$path = "phar://".__DIR__."/phpunit.phar/{$class_name}.php";
	if($path && file_exists($path)){
		/** @noinspection PhpIncludeInspection */
		require_once($path);
	}
}
spl_autoload_register('EtTest\et_phpunit_loader');


function et_tests_loader($class_name){

	if(substr($class_name, 0, 7) != 'EtTest\\'){
		return;
	}

	$class_name = str_replace(array("_", '\\'), "/", substr($class_name, 7));
	$path = __DIR__ . "/{$class_name}.php";

	if($path && file_exists($path)){
		/** @noinspection PhpIncludeInspection */
		require_once($path);
	}
}
spl_autoload_register('EtTest\et_tests_loader');