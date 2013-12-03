<?php
namespace Et;

require_once(__DIR__."/constants.php");
require_once(__DIR__."/functions.php");

// display errors before platform error handler is set
ini_set("display_errors", "on");
error_reporting(E_ALL);

// minimal PHP version check
if (version_compare(PHP_VERSION, ET_MINIMAL_PHP_VERSION, '<')) {
	@header("HTTP/1.1 500 Internal Server Error");
	die("PHP version ".ET_MINIMAL_PHP_VERSION." or above is required for running platform");
}

// set some timezone if not set to avoid date() warnings
ini_set("date.timezone", ET_DEFAULT_TIMEZONE);

// =========================================================================================
// INCLUDE PATH SETUP
// =========================================================================================
set_include_path(
	ET_LIBRARY_PATH . PATH_SEPARATOR .
	ET_LIBRARY_PATH . "External/" . PATH_SEPARATOR .
	get_include_path()
);

// ========================================================================================
// Error handler
// ========================================================================================
et_require("Debug_Error_Handler");
Debug_Error_Handler::enable();
Debug_Error_Handler::getDisplayHandler()->setDisplayHTML(false);

// =========================================================================================
// Et\* Class loader
// =========================================================================================
et_require("Loader");
et_require("Loader_Et");
Loader::enable();
Loader::registerLoader(new Loader_Et());

// =========================================================================================
// Profiler
// =========================================================================================
et_require("Debug_Profiler");

// =========================================================================================
// Application
// =========================================================================================
et_require("Application");
Debug_Profiler::milestone("Init complete");