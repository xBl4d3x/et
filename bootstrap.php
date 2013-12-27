<?php
namespace Et;
require(__DIR__ . "/environment.php");
require(__DIR__ . "/init.php");

System::initialize();
Http_Request::initialize();

$db=DB::get();
var_dump($db->fetchRowsCount(DB::query("dim_channels", array("name", "created_when", "is_private_channel"), [], [], 1)));

echo "ET RUNNING [" . Debug::getDuration() . "s]";

Application::end();
