<?php
namespace Et;

require_once(dirname(__DIR__) . "/init.php");

$modules_metadata = Application_Modules::getModulesMetadata();
foreach($modules_metadata as $module_name => $metadata){
	echo "{$module_name} .. ";
	$metadata->reload();
	echo "done\n";
}

require("modules_list.php");