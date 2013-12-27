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
		return System_Signals::getInstance()->subscribe($signal_name, $signal_handler);
	}

	/**
	 * @param string $subscription_identifier
	 * @return bool
	 */
	public static function unsubscribeSignal($subscription_identifier){
		return System_Signals::getInstance()->unsubscribe($subscription_identifier);
	}
}