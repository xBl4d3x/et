<?php
namespace Et;
class System {

	/**
	 * @var System_Config
	 */
	protected static $config;

	/**
	 * @var System_File_MimeType_Detector
	 */
	protected static $mime_type_detector;

	/**
	 * @var callable[]
	 */
	protected static $on_shutdown_callbacks = array();

	/**
	 * @var bool
	 */
	protected static $shutdown_called = false;

	/**
	 * @param string|System_Config|null $system_environment [optional] NULL = ET_SYSTEM_ENVIRONMENT constant content
	 */
	public static function initialize($system_environment = null){
		if(!$system_environment){
			$system_environment = ET_SYSTEM_ENVIRONMENT;
		}

		et_require("System_Config");
		if(!$system_environment instanceof System_Config){
			$system_environment = new System_Config((string)$system_environment);
		}

		static::$config = $system_environment;
	}

	/**
	 * @return System_Config
	 */
	public static function getConfig(){
		if(!static::$config){
			static::initialize();
		}
		return static::$config;
	}

	/**
	 * @return string
	 */
	public static function getEnvironmentName(){
		return static::getConfig()->getName();
	}



	/**
	 * @return array
	 */
	public static function getOnShutdownCallbacks() {
		return static::$on_shutdown_callbacks;
	}


	/**
	 * @param callable $callback
	 * @param array $callback_arguments [optional]
	 * @return int Callback ID
	 */
	public static function addShutdownCallback(callable $callback, array $callback_arguments = array()){
		$ID = uniqid();
		self::$on_shutdown_callbacks[$ID] = array($callback, $callback_arguments);
		return $ID;
	}

	/**
	 * @param int $callback_ID
	 * @return bool
	 */
	public static function removeShutdownCallback($callback_ID){
		if(isset(self::$on_shutdown_callbacks[$callback_ID])){
			unset(self::$on_shutdown_callbacks[$callback_ID]);
			return true;
		}
		return false;
	}

	/**
	 * Use this instead of exit();
	 */
	public static function shutdown($exit_status = 0){
		if(self::$shutdown_called){
			return;
		}

		self::$shutdown_called = true;
		foreach(self::$on_shutdown_callbacks as $cb){
			list($callback, $arguments) = $cb;
			if(!is_callable($callback)){
				continue;
			}

			call_user_func_array($callback, $arguments);
		}

		exit($exit_status);
	}

	/**
	 * @param bool $enable_implicit_flush [optional]
	 */
	public static function flushOutput($enable_implicit_flush = false){

		while(ob_get_level()) {
			@ob_end_flush();
		}
		@ob_flush();
		@flush();

		if($enable_implicit_flush){
			ob_implicit_flush(true);
		}

	}



	/**
	 * @param System_File_MimeType_Detector $mime_type_detector
	 */
	public static function setMimeTypeDetector(System_File_MimeType_Detector $mime_type_detector) {
		static::$mime_type_detector = $mime_type_detector;
	}

	/**
	 * @return System_File_MimeType_Detector
	 */
	public static function getMimeTypeDetector() {
		if(!static::$mime_type_detector){
			static::$mime_type_detector = new System_File_MimeType_Detector();
		}
		return static::$mime_type_detector;
	}


	/**
	 * @param string $file_path
	 *
	 * @return System_File
	 */
	public static function getFile($file_path){
		return new System_File($file_path);
	}

	/**
	 * @param string $relative_file_path 
	 * @return System_File
	 */
	public static function getDataFile($relative_file_path){
		return static::getFile(ET_DATA_PATH . $relative_file_path);
	}

	/**
	 * @param string $relative_file_path 
	 * @return System_File
	 */
	public static function getPrivateDataFile($relative_file_path){
		return static::getFile(ET_PRIVATE_DATA_PATH . $relative_file_path);
	}

	/**
	 * @param string $relative_file_path 
	 * @return System_File
	 */
	public static function getPublicDataFile($relative_file_path){
		return static::getFile(ET_PUBLIC_DATA_PATH . $relative_file_path);
	}

	/**
	 * @param string $relative_file_path 
	 * @return System_File
	 */
	public static function getSystemDataFile($relative_file_path){
		return static::getFile(ET_SYSTEM_DATA_PATH . $relative_file_path);
	}

	/**
	 * @param string $relative_file_path 
	 * @return System_File
	 */
	public static function getTemporaryDataFile($relative_file_path){
		return static::getFile(ET_TEMPORARY_DATA_PATH . $relative_file_path);
	}

	/**
	 * @param string $relative_file_path 
	 * @return System_File
	 */
	public static function getLogFile($relative_file_path){
		return static::getFile(ET_LOGS_PATH . $relative_file_path);
	}

	/**
	 * @param string $dir_path
	 *
	 * @return System_Dir
	 */
	public static function getDir($dir_path){
		return new System_Dir($dir_path);
	}

	/**
	 * @param null|string $relative_dir_path [optional]
	 * @return System_Dir
	 */
	public static function getDataDir($relative_dir_path = null){
		return static::getDir(ET_DATA_PATH . $relative_dir_path);
	}

	/**
	 * @param null|string $relative_dir_path [optional]
	 * @return System_Dir
	 */
	public static function getPrivateDataDir($relative_dir_path = null){
		return static::getDir(ET_PRIVATE_DATA_PATH . $relative_dir_path);
	}

	/**
	 * @param null|string $relative_dir_path [optional]
	 * @return System_Dir
	 */
	public static function getPublicDataDir($relative_dir_path = null){
		return static::getDir(ET_PUBLIC_DATA_PATH . $relative_dir_path);
	}

	/**
	 * @param null|string $relative_dir_path [optional]
	 * @return System_Dir
	 */
	public static function getSystemDataDir($relative_dir_path = null){
		return static::getDir(ET_SYSTEM_DATA_PATH . $relative_dir_path);
	}

	/**
	 * @param null|string $relative_dir_path [optional]
	 * @return System_Dir
	 */
	public static function getTemporaryDataDir($relative_dir_path = null){
		return static::getDir(ET_TEMPORARY_DATA_PATH . $relative_dir_path);
	}

	/**
	 * @param null|string $relative_dir_path [optional]
	 * @return System_Dir
	 */
	public static function getLogsDir($relative_dir_path = null){
		return static::getDir(ET_LOGS_PATH . $relative_dir_path);
	}

	/**
	 * @param string $text
	 * @param null|string $charset [optional]
	 * @return System_Text
	 */
	public static function getText($text, $charset = null){
		return new System_Text($text, $charset);
	}

	/**
	 * @param string $signal_name
	 * @param callable $signal_handler Callback like function(System_Signals_Signal $signal), if returns FALSE, further signal propagation will be stopped
	 * @return string Subscription identifier
	 */
	public static function subscribeSignal($signal_name, callable $signal_handler){
		return System_Signals::subscribe($signal_name, $signal_handler);
	}

	/**
	 * @param string $subscription_identifier
	 * @return bool
	 */
	public static function unsubscribeSignal($subscription_identifier){
		return System_Signals::unsubscribe($subscription_identifier);
	}

	/**
	 * @param System_Signals_Signal $signal
	 * @return int How many callbacks it passed
	 * @throws System_Signals_Exception
	 */
	public static function publishSignal(System_Signals_Signal $signal){
		return System_Signals::publish($signal);
	}
}