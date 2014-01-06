<?php
namespace Et;
require(__DIR__ . "/environment.php");
require(__DIR__ . "/init.php");

System::initialize();
Http_Request::initialize();
MVC_Modules::initialize();



echo "ET RUNNING [" . Debug::getDuration() . "s]";

Application::end();
