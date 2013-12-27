<?php
define("ET_REQUEST_TIME", isset($_SERVER['REQUEST_TIME_FLOAT']) ? (float)$_SERVER['REQUEST_TIME_FLOAT'] : microtime(true));

// =========================================================================================
// PATHS CONSTANTS SETUP
// =========================================================================================
$base_path = str_replace(DIRECTORY_SEPARATOR, "/", __DIR__) . '/';
define("ET_BASE_PATH", $base_path);

define("ET_DOCS_PATH", "{$base_path}_docs/");
define("ET_INSTALL_PATH", "{$base_path}_install/");
define("ET_TESTS_PATH", "{$base_path}_tests/");

$data_path = "{$base_path}data/";
define("ET_DATA_PATH", $data_path);
define("ET_PRIVATE_DATA_PATH", "{$data_path}private/");
define("ET_PUBLIC_DATA_PATH", "{$data_path}public/");
define("ET_SYSTEM_DATA_PATH", "{$data_path}system/");
define("ET_TEMPORARY_DATA_PATH", "{$data_path}temporary/");

define("ET_CONFIGS_PATH", "{$base_path}configs/");
define("ET_APPLICATIONS_PATH", "{$base_path}applications/");
define("ET_ERROR_PAGES_PATH", "{$base_path}error_pages/");
define("ET_LIBRARY_PATH", "{$base_path}library/");
define("ET_LOGS_PATH", "{$base_path}logs/");
define("ET_MODULES_PATH", "{$base_path}modules/");

$scripts_path = "{$base_path}scripts/";
define("ET_SCRIPTS_PATH", $scripts_path);
define("ET_PRIVATE_SCRIPTS_PATH", "{$scripts_path}private/");
define("ET_PUBLIC_SCRIPTS_PATH", "{$scripts_path}public/");

$static_path = "{$base_path}static/";
define("ET_STATIC_PATH", $static_path);
define("ET_STATIC_FILES_PATH", "{$static_path}files/");
define("ET_STATIC_IMAGES_PATH", "{$static_path}images/");
define("ET_STATIC_JS_PATH", "{$static_path}js/");
define("ET_STATIC_CSS_PATH", "{$static_path}css/");
define("ET_STATIC_LIBS_PATH", "{$static_path}libs/");

$_ENV['TMP'] = ET_TEMPORARY_DATA_PATH;
$_ENV['TMPDIR'] = ET_TEMPORARY_DATA_PATH;
$_ENV['TEMP'] = ET_TEMPORARY_DATA_PATH;


// =========================================================================================
// RUNTIME SETUP
// =========================================================================================
// minimal required PHP version for application
defined("ET_MINIMAL_PHP_VERSION") || define("ET_MINIMAL_PHP_VERSION", "5.4.4");

// default timezone to avoid date() related warnings
if(!defined("ET_DEFAULT_TIMEZONE")){
	$timezone = @ini_get("date.timezone");
	if(!$timezone){
		$timezone = "UTC";
	}
	define("ET_DEFAULT_TIMEZONE", $timezone);
}

// current platform environment - corresponds to [root]/environments/[some_environment].php
defined("ET_SYSTEM_ENVIRONMENT") || define("ET_SYSTEM_ENVIRONMENT", "default");

// default application ID - points to [root]/applications/[application_ID]
defined("ET_DEFAULT_APPLICATION_ID") || define("ET_DEFAULT_APPLICATION_ID", "default");

// default project locale
defined("ET_DEFAULT_LOCALE") || define("ET_DEFAULT_LOCALE", "en_US");

// enables several debugging features (like displaying errors, configs values checking etc. )
defined("ET_DEBUG_MODE") || define("ET_DEBUG_MODE", false);

// enables internal profiling (dispatch times, SQL queries capture .. )
defined("ET_PROFILE_MODE") || define("ET_PROFILE_MODE", false);

// default chmod() permission for writable files
defined("ET_DEFAULT_FILES_CHMOD") || define("ET_DEFAULT_FILES_CHMOD", 0666);

// default chmod() permission for writable directories
defined("ET_DEFAULT_DIRS_CHMOD") || define("ET_DEFAULT_DIRS_CHMOD", 0777);

// default chown() user for writable directories and files
defined("ET_DEFAULT_CHOWN_USER") || define("ET_DEFAULT_CHOWN_USER", "");

// default chgrp() group for writable directories and files
defined("ET_DEFAULT_CHOWN_GROUP") || define("ET_DEFAULT_CHOWN_GROUP", "");

define("ET_CLI_MODE", PHP_SAPI == "cli");
defined("ET_HTTP_PORT") || define("ET_HTTP_PORT", 80);
defined("ET_HTTPS_PORT") || define("ET_HTTPS_PORT", 443);

// request method - GET/POST/.../CLI
define("ET_REQUEST_METHOD", ET_CLI_MODE ? "CLI" : (isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : "GET"));

if(ET_CLI_MODE){
	$_SERVER["HTTP_HOST"] = "cli";
	$php_script = $_SERVER['PHP_SELF'];
	$script_file = realpath(getcwd() . "/" . $php_script);
	$rel_file = substr($script_file, strlen(realpath(__DIR__)));
	if(!$rel_file){
		$rel_file = "/unknown.php";
	}
	$_SERVER["REQUEST_URI"] = $rel_file;
	$_SERVER['DOCUMENT_ROOT'] = __DIR__;
}

$is_HTTPS = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != "off";

if(!isset($_SERVER['HTTP_HOST'])){
	if(!isset($_SERVER['SERVER_NAME'])){
		$_SERVER['SERVER_NAME'] = $_SERVER['SERVER_ADDR'];
	}
	$_SERVER['HTTP_HOST'] = $_SERVER['SERVER_NAME'];
}

if(!preg_match('~^([\w\-\.]+|[\da-f:]+)~', $_SERVER['HTTP_HOST'], $m)){
	@header("HTTP/1.1 500 Internal Server Error");
	die("Cannot determine host name from HTTP host");
}

$host = $m[1];

$port = isset($_SERVER['SERVER_PORT'])
	? $_SERVER['SERVER_PORT']
	: ($is_HTTPS ? ET_HTTPS_PORT : ET_HTTP_PORT);

$base_URI = substr(__DIR__, strlen($_SERVER['DOCUMENT_ROOT']));
if($base_URI === false){
	$base_URI = "/";
}

if(DIRECTORY_SEPARATOR != '/'){
	$base_URI = str_replace(DIRECTORY_SEPARATOR, "/", $base_URI) ;
}

$base_URI = '/' . trim($base_URI, '/') . '/';
if($base_URI == '//'){
	$base_URI = '/';
}

$full_URI = $_SERVER['REQUEST_URI'];
list($clear_URI) = explode("?", $full_URI);
if(substr($clear_URI, -1) != "/" && !preg_match('~\.\w+$~', $clear_URI)){
	if(ET_CLI_MODE){
		$clear_URI .= "/";
	} else {
		$redirect_URI = $clear_URI . "/";
		if($_GET){
			$redirect_URI .= "?" . http_build_query($_GET);
		}
		header("HTTP/1.1 301 Moved Permanently", true, 301);
		header("Location: {$redirect_URI}");
		exit();
	}
}

define("ET_REQUEST_HTTPS", $is_HTTPS);
// server/HTTP host name
define("ET_REQUEST_HOST", $host);
// remote client IP
define("ET_REQUEST_IP", isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : "unknown");
// remote client user agent
define("ET_REQUEST_USER_AGENT", isset($_SERVER['HTTP_USER_AGENT']) ? strip_tags($_SERVER['HTTP_USER_AGENT']) : "unknown");
// remote client referer
define("ET_REQUEST_REFERER", isset($_SERVER['HTTP_REFERER']) ? strip_tags($_SERVER['HTTP_REFERER']) : "");
// server port (usually 80 or 443 for SSL)
define("ET_REQUEST_PORT",  (int)$port);
// HTTP request URI including query part
define("ET_REQUEST_URI_WITH_QUERY", $full_URI);
// HTTP request URI without query part
define("ET_REQUEST_URI_WITHOUT_QUERY", $clear_URI);
// HTTP request query string
define("ET_REQUEST_QUERY_STRING", isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : "");

$scheme_and_host = ET_REQUEST_HTTPS ? "https://" : "http://";
$scheme_and_host .= ET_REQUEST_HOST;
if( (ET_REQUEST_HTTPS && ET_REQUEST_PORT != 443) || (!ET_REQUEST_HTTPS && ET_REQUEST_PORT != 80) ){
	$scheme_and_host .= ":" . ET_REQUEST_PORT;
}
$base_URL = $scheme_and_host . $base_URI;
$base_URL_HTTP = !$is_HTTPS ? $base_URL : "http://{$host}".(ET_HTTP_PORT != 80 ? ":" . ET_HTTP_PORT : "").$base_URI;
$base_URL_HTTPS = $is_HTTPS ? $base_URL : "https://{$host}".(ET_HTTPS_PORT != 443 ? ":" . ET_HTTPS_PORT : "").$base_URI;

// request URL without query part
define("ET_REQUEST_URL_WITHOUT_QUERY", $scheme_and_host . ET_REQUEST_URI_WITHOUT_QUERY);
// request URL with query part
define("ET_REQUEST_URL_WITH_QUERY", $scheme_and_host . ET_REQUEST_URI_WITH_QUERY);

// base URI (URI to [root])
define("ET_BASE_URI", $base_URI);
// base URL (URL to [root])
define("ET_BASE_URL", $base_URL);
define("ET_BASE_URL_HTTP", $base_URL_HTTP);
define("ET_BASE_URL_HTTPS", $base_URL_HTTPS);

// relative request path to base URI without query
define("ET_REQUEST_PATH_WITHOUT_QUERY", substr(ET_REQUEST_URI_WITHOUT_QUERY, strlen($base_URI) - 1));
// relative request path to base URI with query
define("ET_REQUEST_PATH_WITH_QUERY", substr(ET_REQUEST_URI_WITH_QUERY, strlen($base_URI) - 1));

define("ET_DOCS_URI", "{$base_URI}_docs/");
define("ET_APPLICATIONS_URI", "{$base_URI}applications/");
define("ET_SCRIPTS_URI", "{$base_URI}scripts/");
define("ET_PUBLIC_SCRIPTS_URI", ET_SCRIPTS_URI . "public/");
define("ET_SITES_URI", "{$base_URI}sites/");

$static_URI = "{$base_URI}static/";
define("ET_STATIC_URI", $static_URI);
define("ET_STATIC_FILES_URI", "{$static_URI}files/");
define("ET_STATIC_IMAGES_URI", "{$static_URI}images/");
define("ET_STATIC_LIBS_URI", "{$static_URI}libs/");
define("ET_STATIC_JS_URI", "{$static_URI}js/");
define("ET_STATIC_CSS_URI", "{$static_URI}css/");

$data_URI = "{$base_URI}data/";
define("ET_DATA_URI", $data_URI);
define("ET_PUBLIC_DATA_URI", "{$data_URI}public/");
define("ET_MODULES_URI", "{$base_URI}modules/");

define("ET_DOCS_URL", "{$base_URL}_docs/");
define("ET_APPLICATIONS_URL", "{$base_URL}applications/");
define("ET_SCRIPTS_URL", "{$base_URL}scripts/");
define("ET_PUBLIC_SCRIPTS_URL", ET_SCRIPTS_URL . "public/");
define("ET_SITES_URL", "{$base_URL}sites/");

$static_URL = "{$base_URL}static/";
define("ET_STATIC_URL", $static_URL);
define("ET_STATIC_FILES_URL", "{$static_URL}files/");
define("ET_STATIC_IMAGES_URL", "{$static_URL}images/");
define("ET_STATIC_LIBS_URL", "{$static_URL}libs/");
define("ET_STATIC_JS_URL", "{$static_URL}js/");
define("ET_STATIC_CSS_URL", "{$static_URL}css/");

$data_URL = "{$base_URL}data/";
define("ET_DATA_URL", $data_URL);
define("ET_PUBLIC_DATA_URL", "{$data_URL}public/");

define("ET_MODULES_URL", "{$base_URL}modules/");