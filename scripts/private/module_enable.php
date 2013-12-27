<?php
namespace Et;

require_once(dirname(__DIR__) . "/init.php");
if(!isset($argv[1])){
	echo "Usage: " . basename(__FILE__) . " InstalledModuleID[ InstalledModuleID]*\n";
	exit(1);
}

array_shift($argv);
foreach($argv as $module_name){
	if(!Application_Modules::getModuleExists($module_name)){
		echo "Module '{$module_name}' does not exist!\n";
		continue;
	}

	echo "Enabling module '{$module_name}' .. ";
	try {
		$installer = Application_Modules::getModuleInstaller($module_name);
		if(!$installer->getModuleMetadata()->isInstalled()){
			echo "FAILED! Module is not installed\n";
			continue;
		}
		$installer->enableModule();
		echo "DONE\n";
	} catch(Exception $e){
		echo "FAILED! {$e->getMessage()}\n";
		Debug_Error_Handler::getLoggingHandler()->handleError(new Debug_Error($e));
		continue;
	}
}
echo "\n";
require("modules_list.php");