<?php
namespace Et;
require(__DIR__ . "/environment.php");
require(__DIR__ . "/init.php");

Application::initialize();


echo "ET RUNNING [" . Debug::getDuration() . "s]";

Application::end();