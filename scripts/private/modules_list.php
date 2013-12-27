<?php
namespace Et;

require_once(dirname(__DIR__) . "/init.php");

echo "MODULES LIST\n";

$modules_metadata = Application_Modules::getModulesMetadata();
$lines = array();
$lines[] = array(
	"ID",
	"Vendor",
	"Name",
	"Installed",
	"Enabled"
);
foreach($modules_metadata as $module_ID => $metadata){
	$line = array(
				$module_ID,
				$metadata->getVendor(),
				$metadata->getModuleName()
			);

	if($metadata->isInstalled()){
		if($metadata->isOutdated()){
			$line[] = "YES (outdated)";
		} else {
			$line[] = "YES";
		}
	} else {
		$line[] = "NO";
	}
	$line[] = $metadata->isEnabled() ? "YES" : "NO";
	$lines[] = $line;
}

$columns_widths = array();
foreach($lines as &$line){
	foreach($line as $c => &$column){
		if(!isset($columns_widths[$c])){
			$columns_widths[$c] = 0;
		}
		$len = System::getText($column)->getLength() + 2;
		if($columns_widths[$c] < $len){
			$columns_widths[$c] = $len;
		}
	}
}

$columns_count = count($columns_widths);
$line_width = array_sum($columns_widths) + $columns_count + 1;
echo str_repeat("=", $line_width) . "\n";
foreach($lines as $l => &$line){
	foreach($line as $c => &$column){
		$w = $columns_widths[$c];
		if(!$c){
			echo "|";
		}
		echo System::getText(" {$column}")->pad($w, " ", System_Text::PAD_RIGHT);
		echo "|";
	}
	echo "\n";
	if(!$l){
		echo str_repeat("-", $line_width) . "\n";
	}
}
echo str_repeat("=", $line_width) . "\n";