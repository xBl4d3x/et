<?php
namespace Et;

class Debug_Error_Handler {

	const ERROR_REPORTING_LEVEL = E_ALL;

	/**
	 * @var \Et\Debug_Error_Handler_Display
	 */
	protected $display_handler;


	/**
	 * @var \Et\Debug_Error_Handler_Logger
	 */
	protected $logging_handler;


	/**
	 * @var \Et\Debug_Error_Handler_Abstract[]
	 */
	protected $custom_handlers = array();

	/**
	 * @var string
	 */
	protected $error_pages_dir = ET_ERROR_PAGES_PATH;

	/**
	 * IDs of handled errors
	 *
	 * @var array
	 */
	protected $handled_errors_IDs = array();

	/**
	 * Is error handler enabled?
	 *
	 * @var bool
	 */
	protected $enabled = false;

	/**
	 * @var bool
	 */
	protected $registered = false;


	/**
	 * When strict mode is enabled (strict mode level is not 0), throw exception when error matches gives error reporting level
	 *
	 * @var int
	 */
	protected $strict_mode_level;

	function __construct(){
		if(!ini_get("date.timezone")){
			date_default_timezone_set(ET_DEFAULT_TIMEZONE);
		}

		$this->strict_mode_level = $this->getDefaultStrictModeLevel();
	}



	/**
	 * @param int $error_reporting
	 *
	 * @return int Last error reporting level
	 */
	public function setErrorReportingLevel($error_reporting){
		return error_reporting($error_reporting);
	}

	/**
	 * @return int
	 */
	public function getErrorReportingLevel(){
		return error_reporting();
	}

	protected function initPHPLogs(){

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

	}

	/**
	 * @return bool
	 */
	function isRegistered(){
		return $this->registered;
	}

	function register(){

		if($this->registered){
			return;
		}

		error_reporting(static::ERROR_REPORTING_LEVEL);
		$this->initPHPLogs();

		if(!$this->logging_handler){
			et_require("Debug_Error_Handler_Logger");
			$this->logging_handler = new Debug_Error_Handler_Logger();
		}

		if(!$this->display_handler){
			et_require("Debug_Error_Handler_Display");
			$this->display_handler = new Debug_Error_Handler_Display();
		}

		$this->logging_handler->enable();
		if(ET_DEBUG_MODE){
			$this->display_handler->enable();
		} else {
			$this->display_handler->disable();
		}

		$this->display_handler->setDisplayHTML(false);


		set_error_handler(array($this, "handlePHPError"));
		set_exception_handler(array($this, "handleException"));
		register_shutdown_function(array($this, "handleShutdownError"));

		@ini_set("display_errors", "off");
		$this->registered = true;

	}

	/**
	 * Enable error handler
	 */
	public function enable(){

		if(!$this->registered){
			$this->register();
		}

		$this->enabled = true;
	}

	/**
	 * Disable error handler
	 */
	public function disable(){
		$this->enabled = false;
	}

	/**
	 * Is error handler enabled?
	 *
	 * @return bool
	 */
	public function isEnabled(){
		return $this->enabled;
	}

	/**
	 * Get default strict mode level
	 *
	 * @return int
	 */
	public function getDefaultStrictModeLevel() {
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
	public function isStrictModeEnabled(){
		return $this->getStrictModeLevel() > 0;
	}

	/**
	 * @return int Original level
	 */
	public function disableStrictMode(){
		return $this->setStrictModeLevel(0);
	}


	/**
	 * @param null|int $strict_mode_level [optional] If NULL, default strict mode level is used
	 *
	 * @return int
	 */
	public function enableStrictMode($strict_mode_level = null){
		if($strict_mode_level === null){
			$strict_mode_level = $this->getDefaultStrictModeLevel();
		}
		return $this->setStrictModeLevel($strict_mode_level);
	}

	/**
	 * @param int $strict_mode_level
	 *
	 * @return int Old strict mode level
	 */
	public function setStrictModeLevel($strict_mode_level) {
		$last_level = $this->getStrictModeLevel();
		$this->strict_mode_level = max(0, (int)$strict_mode_level);
		return $last_level;
	}

	/**
	 * @return int
	 */
	public function getStrictModeLevel() {
		if($this->strict_mode_level === null){
			$this->strict_mode_level = $this->getDefaultStrictModeLevel();
		}
		return $this->strict_mode_level;
	}




	/**
	 * Returns TRUE if PHP is running in CLI mode
	 *
	 * @return bool
	 */
	public function isCLI(){
		return PHP_SAPI == "cli";
	}

	/**
	 * Sends 500 Internal Server Error header
	 */
	public function sendErrorHeader(){
		if(!$this->isCLI() && !@headers_sent()){
			@header("HTTP/1.1 500 Internal Server Error");
		}
	}

	/**
	 * Returns TRUE if error reporting is disabled (i.e. @ call)
	 *
	 * @return bool
	 */
	public function isErrorReportingDisabled(){
		return $this->getErrorReportingLevel() == 0;
	}

	/**
	 * @param Debug_Error $error
	 */
	public function handleError(Debug_Error $error){

		if(!$this->isEnabled()){
			return;
		}

		$is_fatal = $error->isFatal();
		if($is_fatal){
			$this->sendErrorHeader();
		}

		$logging_handler = $this->getLoggingHandler();
		if($logging_handler && $logging_handler->isEnabled()){
			$logging_handler->handleError($error);
		}

		$display_handler = $this->getDisplayHandler();
		if($display_handler && $display_handler->isEnabled()){
			$display_handler->handleError($error);
		}

		foreach($this->custom_handlers as $handler){
			if(!$handler->isEnabled()){
				continue;
			}
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

		if($this->isCLI()){
			echo "FATAL ERROR OCCURRED, see error logs for detail";
			exit(1);
		}

		@ob_end_clean();

		$error_page_location = $this->getErrorPagePath();
		if(!$error_page_location || !file_exists($error_page_location)){
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
	public function handleShutdownError(){

		$last_error = error_get_last();
		if(!$last_error || !is_array($last_error) || !$this->isEnabled()){
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

		et_require("Debug_PHPError");
		$exception = new Debug_PHPError(
			$error_string,
			array(),
			$error_number,
			$script,
			$line_number
		);


		// if error reporting disabled (i.e. @ before expression) and is not fatal error, ignore it
		if(!($this->getErrorReportingLevel() & $error_number) && !$exception->isFatal()){
			return;
		}

		// unique error ID to avoid loop-like errors to be displayed/logged many times
		$error_ID = $exception->getErrorID();
		if(isset($this->handled_errors_IDs[$error_ID])){
			return;
		}

		$this->handled_errors_IDs[$error_ID] = $error_ID;

		et_require("Debug_Error");
		$error = new Debug_Error($exception);
		$error->setOccurredOnShutdown(true);
		$error->setStrictModeEnabled($this->isStrictModeEnabled());
		$this->handleError($error);
	}


	/**
	 * @param \Exception $exception
	 *
	 * @return bool
	 */
	public function handleException(\Exception $exception){
		if(!$this->enabled){
			return false;
		}

		et_require("Debug_Error");
		$error = new Debug_Error($exception);
		$error->setStrictModeEnabled($this->isStrictModeEnabled());
		$this->handleError($error);

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
	 * @throws \Et\Debug_PHPError
	 * @return bool
	 */
	public function handlePHPError($error_number, $error_string, $script, $line_number, $error_context){

		if(!$this->enabled){
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

		if(!is_array($error_context)){
			$error_context = array();
		}

		et_require("Debug_PHPError");
		$exception = new Debug_PHPError(
			$error_string,
			$error_context,
			$error_number,
			$script,
			$line_number,
			2
		);

		$is_fatal = $exception->isFatal();

		// if error reporting disabled (i.e. @ before expression) and is not fatal error, ignore it
		if(!($this->getErrorReportingLevel() & $error_number) && !$is_fatal){
			return true;
		}

		if($this->getStrictModeLevel() & $error_number){
			throw $exception;
		}

		// unique error ID to avoid loop-like errors to be displayed/logged many times
		$error_ID = $exception->getErrorID();
		if(isset($this->handled_errors_IDs[$error_ID])){
			return true;
		}

		$this->handled_errors_IDs[$error_ID] = $error_ID;

		et_require("Debug_Error");
		$error = new Debug_Error($exception);
		$error->setStrictModeEnabled($this->isStrictModeEnabled());
		$this->handleError($error);

		return true;
	}

	/**
	 * @param Debug_Error_Handler_Logger $logging_handler
	 */
	public function setLoggingHandler(Debug_Error_Handler_Logger $logging_handler) {
		$this->logging_handler = $logging_handler;
	}

	/**
	 * @return Debug_Error_Handler_Logger
	 */
	public function getLoggingHandler() {
		return $this->logging_handler;
	}

	/**
	 * @param Debug_Error_Handler_Display $display_handler
	 */
	public function setDisplayHandler(Debug_Error_Handler_Display $display_handler) {
		$this->display_handler = $display_handler;
	}

	/**
	 * @return Debug_Error_Handler_Display
	 */
	public function getDisplayHandler() {
		return $this->display_handler;
	}

	/**
	 * @param \Et\Debug_Error_Handler_Abstract $handler
	 *
	 * @return string
	 */
	public function addCustomHandler(Debug_Error_Handler_Abstract $handler){

		$ID = get_class($handler) . ":" . spl_object_hash($handler);
		if(!isset($this->custom_handlers[$ID])){
			$this->custom_handlers[$ID] = $handler;
		}
		return $ID;
	}

	/**
	 * @param string $handler_ID
	 *
	 * @return bool
	 */
	public function removeCustomHandler($handler_ID){
		if(isset($this->custom_handlers[$handler_ID])){
			unset($this->custom_handlers[$handler_ID]);
			return true;
		}
		return false;
	}


	/**
	 * @param string $handler_ID
	 *
	 * @return bool|\Et\Debug_Error_Handler_Abstract
	 */
	public function getCustomHandler($handler_ID){
		return isset($this->custom_handlers[$handler_ID])
				? $this->custom_handlers[$handler_ID]
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
				$row["file"] = "?";
			}

			if(count($output) >= $max_steps){
				break;
			}

			$item = array(
				"file" => str_replace(array("\\", DIRECTORY_SEPARATOR), "/", $row["file"]),
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
	public function setErrorPagesDir($error_pages_dir) {
		$error_pages_dir = str_replace(array("\\", DIRECTORY_SEPARATOR), "/", trim($error_pages_dir));
		$this->error_pages_dir = rtrim($error_pages_dir, "/") . "/";
	}

	/**
	 * @return string
	 */
	public function getErrorPagesDir() {
		return $this->error_pages_dir;
	}

	/**
	 * @param int $error_code [optional]
	 *
	 * @return bool|string
	 */
	public function getErrorPagePath($error_code = 500){
		$error_pages_dir = $this->getErrorPagesDir();
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