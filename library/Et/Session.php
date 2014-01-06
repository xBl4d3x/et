<?php
namespace Et;
class Session {

	const DEFAULT_NAMESPACE = "__default__";
	const NAMESPACE_KEY_PREFIX = "___NS_";
	const METADATA_NAMESPACE = "___metadata___";

	/**
	 * @var Session_Config
	 */
	protected static $session_config;

	/**
	 * @var \Et\Session_Namespace[]
	 */
	protected static $namespaces = array();


	/**
	 * @return bool
	 */
	public static function getSessionStarted(){
		if(ET_CLI_MODE){
			return false;
		}

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

	public static function closeSession(){
		@session_write_close();
	}


	/**
	 * @throws Session_Exception
	 */
	public static function startSession($session_ID = null){

		$session_started = static::getSessionStarted();
		$cfg = static::getSessionConfig();

		if(!$session_started){
			// initialize session settings
			$ini_settings = $cfg->toIniSettings();

			foreach($ini_settings as $key => $value){
				ini_set($key, $value);
			}
		}

		if(!ET_CLI_MODE){

			try {

				if($session_started){
					self::closeSession();
				}

				if($session_ID){
					session_id($session_ID);
				}

				if(!session_start()){
					Debug::triggerError("session_start() returned FALSE");
				}

			} catch(Debug_PHPError $e){
				throw new Session_Exception(
					"Failed to start session - {$e->getMessage()}",
					Session_Exception::CODE_SESSION_START_FAILED
				);
			}
		}

		static::getSessionMetadata()->sessionAccessed();

		if($cfg->getSessionHijackingDetectionEnabled()){
			static::checkSessionHijacked();
		}
	}



	protected static function checkSessionHijacked(){
		$metadata = static::getSessionMetadata();
		if($metadata->getCreatedByIP() == ET_REQUEST_IP){
			return;
		}

		$ignore_list = static::getSessionConfig()->getSessionHijackingIPIgnoreList();
		foreach($ignore_list as $rule){
			$rule = str_replace(array("*", "?"), array('\d+', '\d'), $rule);
			if(preg_match('~^'.$rule.'$~', ET_REQUEST_IP)){
				return;
			}
		}

		//toto: zalogovat ...

		static::destroySession();
		static::startSession();
	}

	/**
	 * @return Session_Config
	 */
	public static function getSessionConfig() {
		if(!static::$session_config){
			static::$session_config = Session_Config::getFromSystemConfig();
		}
		return static::$session_config;
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
	public static function getNamespace($namespace_name = null){
		if(!$namespace_name){
			$namespace_name = self::DEFAULT_NAMESPACE;
		}

		if(isset(self::$namespaces[$namespace_name])){
			return self::$namespaces[$namespace_name];
		}

		self::$namespaces[$namespace_name] = new Session_Namespace($namespace_name);
		return self::$namespaces[$namespace_name];
	}

	/**
	 * @param string $namespace_name
	 * @return bool
	 */
	public static function getNamespaceExists($namespace_name){
		if(!static::getSessionStarted()){
			static::startSession();
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
			if(isset(self::$namespaces[$namespace_name])){
				unset(self::$namespaces[$namespace_name]);
			}
			return true;
		}
		return false;
	}

}