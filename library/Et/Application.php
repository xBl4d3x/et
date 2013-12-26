<?php
namespace Et;
et_require('Object');
class Application extends Object {

	/**
	 * @var Application_Config
	 */
	protected static $config;

	/**
	 * @var array
	 */
	protected static $on_end_callbacks = array();

	/**
	 * @var int
	 */
	protected static $on_end_callbacks_counter = 0;

	/**
	 * @var bool
	 */
	protected static $end_called = false;

	/**
	 * @var Locales_Locale
	 */
	protected static $application_locale;

	/**
	 * @var Locales_Timezone
	 */
	protected static $application_timezone;

	/**
	 * @var Locales_Locale
	 */
	protected static $user_locale;

	/**
	 * @var Locales_Timezone
	 */
	protected static $user_timezone;

	/**
	 * @param string|Application_Config|null $environment [optional] NULL = ET_APPLICATION_ENVIRONMENT constant content
	 */
	public static function initialize($environment = null){
		if(!$environment){
			$environment = ET_APPLICATION_ENVIRONMENT;
		}

		et_require("Application_Config");
		if(!$environment instanceof Application_Config){
			$environment = new Application_Config((string)$environment);
		}

		static::$config = $environment;
		register_shutdown_function(array(get_called_class(), "end"));

	}

	/**
	 * @return Application_Config
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
	 * @param callable $callback
	 * @param array $callback_arguments [optional]
	 * @return int Callback ID
	 */
	public static function addOnEndCallback(callable $callback, array $callback_arguments = array()){
		$ID = ++static::$on_end_callbacks_counter;
		self::$on_end_callbacks[$ID] = array($callback, $callback_arguments);
		return $ID;
	}

	/**
	 * @param int $callback_ID
	 * @return bool
	 */
	public static function removeOnEndCallback($callback_ID){
		if(isset(self::$on_end_callbacks[$callback_ID])){
			unset(self::$on_end_callbacks[$callback_ID]);
			return true;
		}
		return false;
	}

	/**
	 * Use this instead of exit();
	 */
	public static function end($exit = true){
		if(self::$end_called){
			return;
		}

		self::$end_called = true;
		foreach(self::$on_end_callbacks as $cb){
			list($callback, $arguments) = $cb;
			if(!is_callable($callback)){
				continue;
			}

			call_user_func_array($callback, $arguments);
		}

		if($exit){
			exit();
		}
	}

	/**
	 * @param \Et\Locales_Locale|string $application_locale
	 */
	public static function setApplicationLocale($application_locale) {
		static::$application_locale = Locales::getLocale($application_locale);
	}

	/**
	 * @return \Et\Locales_Locale
	 */
	public static function getApplicationLocale() {
		if(!static::$application_locale){
			if(static::$user_locale){
				static::$application_locale = static::$user_locale;
			} else {
				static::$application_locale = Locales::getLocale(ET_DEFAULT_LOCALE);
			}
		}
		return static::$application_locale;
	}

	/**
	 * @param \Et\Locales_Timezone $application_timezone
	 */
	public static function setApplicationTimezone($application_timezone) {
		static::$application_timezone = Locales::getTimezone($application_timezone);
	}

	/**
	 * @return \Et\Locales_Timezone
	 */
	public static function getApplicationTimezone() {
		if(!static::$application_timezone){
			if(static::$user_timezone){
				static::$application_timezone = static::$user_timezone;
			} else {
				static::$application_timezone = Locales::getSystemTimezone();
			}
		}
		return static::$application_timezone;
	}

	/**
	 * @param \Et\Locales_Timezone $user_timezone
	 */
	public static function setUserTimezone($user_timezone) {
		static::$user_timezone = Locales::getTimezone($user_timezone);
	}

	/**
	 * @return \Et\Locales_Timezone
	 */
	public static function getUserTimezone() {
		if(!static::$user_timezone){
			static::$user_timezone = static::getApplicationTimezone();
		}
		return static::$user_timezone;
	}

	/**
	 * @param \Et\Locales_Locale|string $user_locale
	 */
	public static function setUserLocale($user_locale) {
		static::$user_locale = Locales::getLocale($user_locale);
	}

	/**
	 * @return \Et\Locales_Locale
	 */
	public static function getUserLocale() {
		if(!static::$user_locale){
			return static::getApplicationLocale();
		}
		return static::$user_locale;
	}

	/**
	 * @return array
	 */
	public static function getOnEndCallbacks() {
		return static::$on_end_callbacks;
	}




}
