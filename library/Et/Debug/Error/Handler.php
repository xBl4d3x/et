<?php
namespace Et;
class Debug_Error_Handler {

	const ERROR_REPORTING_LEVEL = E_ALL;

	/**
	 * @var Debug_Error_Handler_Display
	 */
	protected static $display_handler;


	/**
	 * @var Debug_Error_Handler_Logger
	 */
	protected static $logging_handler;


	/**
	 * @var Debug_Error_Handler_Abstract[]
	 */
	protected static $custom_handlers = array();

	/**
	 * @var string
	 */
	protected static $error_pages_dir = ET_ERROR_PAGES_PATH;

	/**
	 * IDs of handled errors
	 *
	 * @var array
	 */
	protected static $handled_errors_IDs = array();

	/**
	 * Is error handler enabled?
	 *
	 * @var bool
	 */
	protected static $enabled = false;

	/**
	 * @var bool
	 */
	protected static $initialized = false;

	/**
	 * When strict mode is enabled (strict mode level is not 0), throw exception when error matches gives error reporting level
	 *
	 * @var int
	 */
	protected static $strict_mode_level;

	/**
	 * Initialize error handler
	 */
	public static function initialize(){
		if(static::$initialized){
			return;
		}

		static::$initialized = true;

		error_reporting(static::ERROR_REPORTING_LEVEL);

		if(!ini_get("date.timezone")){
			date_default_timezone_set(ET_DEFAULT_TIMEZONE);
		}

		$php_errors_dir = ET_LOGS_PATH . "php_error_log/";
		if(!file_exists($php_errors_dir)){
			@mkdir($php_errors_dir, ET_DEFAULT_DIRS_CHMOD);
			if(ET_DEFAULT_CHOWN_GROUP){
				@chgrp($php_errors_dir, ET_DEFAULT_CHOWN_GROUP);
			}
			if(ET_DEFAULT_CHOWN_USER){
				@chown($php_errors_dir, ET_DEFAULT_CHOWN_USER);
			}
		}

		$error_log_file = $php_errors_dir . date("Y-m-d") . ".log";
		if(file_exists($error_log_file) && @is_writable($error_log_file)){
			@ini_set("error_log", $error_log_file);
		} elseif(file_exists($php_errors_dir) && @is_writable($php_errors_dir)){
			@ini_set("error_log", $error_log_file);
		}


		static::getLoggingHandler();
		$display_handler = static::getDisplayHandler();


		if(!ET_DEBUG_MODE){
			$display_handler->disable();
		}

		if(PHP_SAPI == "cli"){
			$display_handler->setDisplayHTML(false);
		}

		$class = get_called_class();
		set_error_handler(array($class, "handleError"));
		set_exception_handler(array($class, "handleException"));
		register_shutdown_function(array($class, "handleShutdownError"));

		@ini_set("display_errors", "off");
	}





	/**
	 * @param int $error_reporting
	 *
	 * @return int Last error reporting level
	 */
	public static function setErrorReportingLevel($error_reporting){
		return error_reporting($error_reporting);
	}

	/**
	 * @return int
	 */
	public static function getErrorReportingLevel(){
		return error_reporting();
	}

	/**
	 * Enable error handler
	 */
	public static function enable(){
		if(static::$enabled){
			return;
		}

		if(!static::$initialized){
			static::initialize();
		}

		static::$enabled = true;
	}

	/**
	 * Disable error handler
	 */
	public static function disable(){
		if(!static::$enabled){
			return;
		}
		static::$enabled = false;
	}

	/**
	 * Is error handler enabled?
	 *
	 * @return bool
	 */
	public static function isEnabled(){
		return static::$enabled;
	}

	/**
	 * Get default strict mode level
	 *
	 * @return int
	 */
	public static function getDefaultStrictModeLevel() {
		return E_ALL &
		       ~E_NOTICE &
		       ~E_USER_NOTICE &
		       ~E_DEPRECATED &
		       ~E_USER_DEPRECATED &
		       ~E_STRICT;
	}

	/**
	 * @return bool
	 */
	public static function isStrictModeEnabled(){
		return static::getStrictModeLevel() > 0;
	}

	/**
	 * @return int Original level
	 */
	public static function disableStrictMode(){
		return static::setStrictModeLevel(0);
	}


	/**
	 * @param null|int $strict_mode_level [optional] If NULL, default strict mode level is used
	 *
	 * @return int
	 */
	public static function enableStrictMode($strict_mode_level = null){
		if($strict_mode_level === null){
			$strict_mode_level = static::getDefaultStrictModeLevel();
		}
		return static::setStrictModeLevel($strict_mode_level);
	}

	/**
	 * @param int $strict_mode_level
	 *
	 * @return int Old strict mode level
	 */
	public static function setStrictModeLevel($strict_mode_level) {
		$last_level = static::getStrictModeLevel();
		static::$strict_mode_level = max(0, (int)$strict_mode_level);
		return $last_level;
	}

	/**
	 * @return int
	 */
	public static function getStrictModeLevel() {
		if(static::$strict_mode_level === null){
			static::$strict_mode_level = static::getDefaultStrictModeLevel();
		}
		return static::$strict_mode_level;
	}




	/**
	 * Returns TRUE if PHP is running in CLI mode
	 *
	 * @return bool
	 */
	public static function isCLI(){
		return PHP_SAPI == "cli";
	}

	/**
	 * Sends 500 Internal Server Error header
	 */
	public static function sendErrorHeader(){
		if(!static::isCLI() && !@headers_sent()){
			@header("HTTP/1.1 500 Internal Server Error");
		}
	}

	/**
	 * Returns TRUE if error reporting is disabled (i.e. @ call)
	 *
	 * @return bool
	 */
	public static function isErrorReportingDisabled(){
		return static::getErrorReportingLevel() == 0;
	}

	/**
	 * @param Debug_Error $error
	 */
	public static function handleErrorInstance(Debug_Error $error){
		$is_fatal = $error->isFatal();
		if($is_fatal){
			static::sendErrorHeader();
		}

		static::getLoggingHandler()->handleError($error);
		static::getDisplayHandler()->handleError($error);

		foreach(static::$custom_handlers as $handler){
			$handler->handleError($error);
		}

		if($error->isDisplayed()){
			if($is_fatal){
				exit(1);
			}
			return;
		}

		if(!$is_fatal){
			return;
		}

		if(static::isCLI()){
			echo "FATAL ERROR OCCURRED, see error logs for detail";
			exit(1);
		}

		@ob_end_clean();

		$error_page_location = static::getErrorPagePath();
		if(!$error_page_location){
			echo "FATAL ERROR OCCURRED, see error logs for detail";
			exit(1);
		}

		/** @noinspection PhpIncludeInspection */
		include($error_page_location);

		exit(1);
	}

	/**
	 * Handles errors which occurred during shutdown stage
	 */
	public static function handleShutdownError(){

		$last_error = error_get_last();
		if(!$last_error || !is_array($last_error) || !static::$enabled){
			return;
		}

		$error_number = $last_error["type"];
		$error_string = $last_error["message"];
		$script = $last_error["file"];
		$line_number = $last_error["line"];


		// deprecated - ignore
		if ($error_number == E_DEPRECATED) {
			return;
		}

		// strict error in standard PEAR - ignore
		if (
			$error_number == E_STRICT &&
			strpos($script, "PEAR") !== false
		) {
			return;
		}

		et_require('Debug_Error');
		et_require('Exception_PHPError');


		$exception = new Exception_PHPError(
			$error_string,
			array(),
			$error_number,
			$script,
			$line_number
		);


		// if error reporting disabled (i.e. @ before expression) and is not fatal error, ignore it
		if(!(static::getErrorReportingLevel() & $error_number) && !$exception->isFatal()){
			return;
		}

		// unique error ID to avoid loop-like errors to be displayed/logged many times
		$error_ID = $exception->getErrorID();
		if(isset(static::$handled_errors_IDs[$error_ID])){
			return;
		}

		static::$handled_errors_IDs[$error_ID] = $error_ID;

		$error = new Debug_Error($exception);
		$error->setOccurredOnShutdown(true);
		$error->setStrictModeEnabled(static::isStrictModeEnabled());
		static::handleErrorInstance($error);
	}


	/**
	 * @param \Exception $exception
	 *
	 * @return bool
	 */
	public static function handleException(\Exception $exception){
		if(!static::$enabled){
			return false;
		}

		et_require('Debug_Error');
		$error = new Debug_Error($exception);
		$error->setStrictModeEnabled(static::isStrictModeEnabled());
		static::handleErrorInstance($error);

		return true;
	}


	/**
	 * PHP error handler - errors are router to registered error handlers instances
	 *
	 * @param int $error_number
	 * @param string $error_string
	 * @param string $script
	 * @param int $line_number
	 * @param array $error_context
	 *
	 * @throws Exception_PHPError
	 * @return bool
	 */
	public static function handleError($error_number, $error_string, $script, $line_number, $error_context){

		if(!static::$enabled){
			return false;
		}

		// deprecated - ignore
		if ($error_number == E_DEPRECATED) {
			return true;
		}

		// strict error in standard PEAR - ignore
		if (
			$error_number == E_STRICT &&
			stripos($script, "PEAR") !== false
		) {
			return true;
		}

		// skip overloaded methods with different interface
		if($error_number == E_STRICT && strpos($error_string, "should be compatible with") !== false){
			return true;
		}

		et_require('Debug_Error');
		et_require('Exception_PHPError');

		if(!is_array($error_context)){
			$error_context = array();
		}

		$exception = new Exception_PHPError(
			$error_string,
			$error_context,
			$error_number,
			$script,
			$line_number,
			2
		);

		$is_fatal = $exception->isFatal();

		// if error reporting disabled (i.e. @ before expression) and is not fatal error, ignore it
		if(!(static::getErrorReportingLevel() & $error_number) && !$is_fatal){
			return true;
		}

		if(static::getStrictModeLevel() & $error_number){
			throw $exception;
		}

		// unique error ID to avoid loop-like errors to be displayed/logged many times
		$error_ID = $exception->getErrorID();
		if(isset(static::$handled_errors_IDs[$error_ID])){
			return true;
		}

		static::$handled_errors_IDs[$error_ID] = $error_ID;

		$error = new Debug_Error($exception);
		$error->setStrictModeEnabled(static::isStrictModeEnabled());
		static::handleErrorInstance($error);

		return true;
	}

	/**
	 * @param Debug_Error_Handler_Logger $logging_handler
	 */
	public static function setLoggingHandler(Debug_Error_Handler_Logger $logging_handler) {
		static::$logging_handler = $logging_handler;
	}

	/**
	 * @return Debug_Error_Handler_Logger
	 */
	public static function getLoggingHandler() {
		if(!static::$logging_handler){
			et_require('Debug_Error_Handler_Logger');
			static::$logging_handler = new Debug_Error_Handler_Logger();
		}
		return static::$logging_handler;
	}

	/**
	 * @param Debug_Error_Handler_Display $display_handler
	 */
	public static function setDisplayHandler(Debug_Error_Handler_Display $display_handler) {
		static::$display_handler = $display_handler;
	}

	/**
	 * @return Debug_Error_Handler_Display
	 */
	public static function getDisplayHandler() {
		if(!static::$display_handler){
			et_require('Debug_Error_Handler_Display');
			static::$display_handler = new Debug_Error_Handler_Display();
		}
		return static::$display_handler;
	}

	/**
	 * @param Debug_Error_Handler_Abstract $handler
	 *
	 * @return int
	 */
	public static function addCustomHandler(Debug_Error_Handler_Abstract $handler){
		foreach(static::$custom_handlers as $k => $h){
			if($h === $handler){
				return $k;
			}
		}
		$keys = array_keys(static::$custom_handlers);
		$next_key = $keys
				  ? max($keys) + 1
				  : 0;
		static::$custom_handlers[$next_key] = $handler;
		return $next_key;
	}

	/**
	 * @param int $handler_ID
	 *
	 * @return bool
	 */
	public static function removeCustomHandler($handler_ID){
		if(isset(static::$custom_handlers[$handler_ID])){
			unset(static::$custom_handlers[$handler_ID]);
			return true;
		}
		return false;
	}


	/**
	 * @param int $handler_ID
	 *
	 * @return bool|Debug_Error_Handler_Abstract
	 */
	public static function getCustomHandler($handler_ID){
		return isset(static::$custom_handlers[$handler_ID])
				? static::$custom_handlers[$handler_ID]
				: false;
	}


	/**
	 * @param array $backtrace
	 * @param int $from_index [optional]
	 * @param null|int $max_steps [optional]
	 *
	 * @return Debug_Error
	 */
	public static function normalizeBacktrace(array $backtrace, $from_index = 0, $max_steps = null){

		if((int)$max_steps <= 0){
			$max_steps = count($backtrace);
		}

		$output = array();
		$from_index = max(0, min(count($backtrace) - 1, (int)$from_index));

		$trace_count = count($backtrace);
		for($i = $from_index; $i < $trace_count; $i++){
			$row = $backtrace[$i];
			if(!isset($row["file"])){
				continue;
			}

			if(count($output) >= $max_steps){
				break;
			}

			$item = array(
				"file" => System_Path::normalizeFilePath($row["file"]),
				"line" => isset($row["line"]) ? $row["line"] : 0,
				"class" => isset($row["class"]) ? $row["class"] : null,
				"object" => isset($row["object"]) ? $row["object"] : null,
				"object_class" => isset($row["object"]) ? get_class($row["object"]) : null,
				"type" => isset($row["type"]) ? $row["type"] : null,
				"function" => isset($row["function"]) ? $row["function"] : null,
				"method" => null,
				"args" => isset($row["args"]) ? $row["args"] : array(),
			);

			if($item["function"] && $item["type"]){
				if($item["object_class"]) {
					$item["method"] = "{$item["object_class"]}{$item["type"]}{$item["function"]}";
				} elseif($item["class"]){
					$item["method"] = "{$item["class"]}{$item["type"]}{$item["function"]}";
				}
			}

			$output[] = $item;
		}
		return $output;
	}

	/**
	 * @param string $error_pages_dir
	 *
	 */
	public static function setErrorPagesDir($error_pages_dir) {
		static::$error_pages_dir = rtrim((string)$error_pages_dir, "\\/") . "/";
	}

	/**
	 * @return string
	 */
	public static function getErrorPagesDir() {
		return static::$error_pages_dir;
	}

	/**
	 * @param int $error_code [optional]
	 *
	 * @return bool|string
	 */
	public static function getErrorPagePath($error_code = 500){
		$error_pages_dir = static::getErrorPagesDir();
		$page_path = $error_pages_dir . $error_code . ".phtml";
		if(file_exists($page_path)){
			return $page_path;
		}

		if(file_exists(ET_ERROR_PAGES_PATH . $error_code . ".phtml")){
			return ET_ERROR_PAGES_PATH . $error_code . ".phtml";
		}

		return $page_path;
	}
}