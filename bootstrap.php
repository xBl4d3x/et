<?php
namespace Et;
require(__DIR__ . "/environment.php");
require(__DIR__ . "/init.php");
Debug_Assert::isIP("127.0.0.1", false, true);

System::initialize();
Http_Request::initialize();


echo "ET RUNNING [" . Debug::getDuration() . "s]";

Application::end();
