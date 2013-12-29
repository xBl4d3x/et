<?php
namespace Et;
require(__DIR__ . "/environment.php");
require(__DIR__ . "/init.php");

System::initialize();
Http_Request::initialize();
$transliterator = \Transliterator::create("Any-Latin; NFD; [:Nonspacing Mark:] Remove; NFC; [:Punctuation:] Remove; Lower();");
var_dump($transliterator->transliterate("Ελληνικά νέα"));

echo "ET RUNNING [" . Debug::getDuration() . "s]";

Application::end();
