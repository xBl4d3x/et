<?php
namespace Et;

require_once(dirname(__DIR__) . "/init.php");
if(!isset($argv[1])){
	echo "Usage: " . basename(__FILE__) . " ExistingModuleID[ ExistingModuleID]*\n";
	exit(1);
}

array_shift($argv);
foreach($argv as $module_name){
	if(!Application_Modules::getModuleExists($module_name)){
		echo "Module '{$module_name}' does not exist!\n";
		continue;
	}
	echo "Installing module '{$module_name}' .. ";
	try {
		$installer = Application_Modules::getModuleInstaller($module_name);
		if($installer->getModuleMetadata()->isInstalled()){
			echo "already installed\n";
			continue;
		}
		$installer->installModule();
		echo "DONE\n";
	} catch(Exception $e){
		echo "FAILED! {$e->getMessage()}\n";
		Debug_Error_Handler::getLoggingHandler()->handleError(new Debug_Error($e));
		continue;
	}
}
echo "\n";
require("modules_list.php");