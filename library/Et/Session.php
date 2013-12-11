<?php
namespace Et;
class Session extends Object {

	const NAMESPACE_KEY_PREFIX = "___NS_";
	const DEFAULT_NAMESPACE = "___default___";
	const METADATA_NAMESPACE = "___metadata___";

	/**
	 * @var bool
	 */
	protected static $is_initialized = false;

	/**
	 * @var Session_Config
	 */
	protected static $config;

	/**
	 * @var Debug_Profiler_Default
	 */
	protected static $profiler;

	/**
	 * @return bool
	 */
	public static function isInitialized(){
		return static::$is_initialized;
	}

	/**
	 * @return bool
	 */
	public static function getSessionStarted(){
		return session_status() == PHP_SESSION_ACTIVE && session_id();
	}

	/**
	 * @return bool|string
	 */
	public static function getSessionID(){
		if(!static::getSessionStarted()){
			return false;
		}
		return session_id();
	}

	/**
	 * @return bool
	 */
	public static function destroySession(){
		return @session_destroy();
	}

	/**
	 * @return \Et\Debug_Profiler_Default
	 */
	public static function getProfiler() {
		if(!static::$profiler){
			static::$profiler = new Debug_Profiler_Default("Session handler");
			Debug_Profiler::addProfiler(static::$profiler);
		}
		return static::$profiler;
	}




	/**
	 * @throws Session_Exception
	 */
	public static function initialize(){
		if(static::$is_initialized){
			return;
		}

		$cfg = static::getConfig();
		$ini_settings = $cfg->toIniSettings();
		static::getProfiler()->milestone("Initializing session");


		foreach($ini_settings as $key => $value){
			ini_set($key, $value);
		}

		if(!static::getSessionStarted()){
			try {
				if(!session_start()){
					Exception_PHPError::triggerError("session_start() returned FALSE");
				}
			} catch(Exception_PHPError $e){
				throw new Session_Exception(
					"Failed to start session - {$e->getMessage()}",
					Session_Exception::CODE_SESSION_START_FAILED
				);
			}
		}
		static::getProfiler()->milestone("Session started");

		static::$is_initialized = true;

		static::getSessionMetadata()->sessionAccessed();

		if($cfg->getSessionHijackingDetectionEnabled()){
			static::checkSessionHijacked();
		}
	}



	protected static function checkSessionHijacked(){
		$metadata = static::getSessionMetadata();
		if($metadata->getCreatedByIP() == ET_REQUEST_IP){
			static::getProfiler()->milestone("Session owner checked");
			return;
		}

		$ignore_list = static::getConfig()->getSessionHijackingIPIgnoreList();
		foreach($ignore_list as $rule){
			$rule = str_replace(array("*", "?"), array('\d+', '\d'), $rule);
			if(preg_match('~^'.$rule.'$~', ET_REQUEST_IP)){
				static::getProfiler()->milestone("Invalid session owner but ignored");
				return;
			}
		}

		//toto: zalogovat ...

		static::destroySession();
		static::getProfiler()->milestone("Invalid session owner - session destroyed");
		static::$is_initialized = false;
		static::initialize();
	}

	/**
	 * @return Session_Config
	 */
	public static function getConfig() {
		if(!static::$config){
			static::$config = Session_Config::getFromEnvironmentConfig();
		}
		return static::$config;
	}

	/**
	 * @return Session_Metadata
	 */
	public static function getSessionMetadata(){
		return new Session_Metadata();
	}


	/**
	 * @param null|string $namespace_name [optional]
	 * @return Session_Namespace
	 */
	public static function get($namespace_name = null){
		if(!$namespace_name){
			$namespace_name = static::DEFAULT_NAMESPACE;
		}
		return new Session_Namespace($namespace_name);
	}

	/**
	 * @param string $namespace_name
	 * @return bool
	 */
	public static function getNamespaceExists($namespace_name){
		if(!static::isInitialized()){
			static::initialize();
		}
		return isset($_SESSION[static::getNamespaceSessionKey($namespace_name)]);
	}

	/**
	 * @param string $namespace_name
	 * @return string
	 */
	public static function getNamespaceSessionKey($namespace_name){
		return static::NAMESPACE_KEY_PREFIX.$namespace_name;
	}

	/**
	 * @param string $namespace_name
	 * @return bool
	 */
	public static function removeNamespace($namespace_name){
		if(static::getNamespaceExists($namespace_name)){
			unset($_SESSION[static::getNamespaceSessionKey($namespace_name)]);
			return true;
		}
		return false;
	}

}