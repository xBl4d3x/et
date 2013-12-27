<?php
namespace Et;
et_require('Object');
class Application extends Object {

	const SYSTEM_COMPONENTS_TYPE = "applications";

	/**
	 * @var Application_Abstract
	 */
	protected static $current_application;

	/**
	 * @var array
	 */
	protected static $application_IDs = array();

	/**
	 * @var Application_Metadata[]|System_Components_List
	 */
	protected static $applications_metadata = array();

	/**
	 * @var Application_Abstract[]
	 */
	protected static $application_instances = array();


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
	 * @var bool
	 */
	protected static $initialized = false;


	public static function initialize(){

		if(static::$initialized){
			return;
		}

		Loader::registerLoader(new Application_Loader());

		$applications_dir = System::getDir(ET_APPLICATIONS_PATH);
		$application_IDs = $applications_dir->listDirNames(
			function($dir_name){
				return (bool)preg_match('~^\w+$~', $dir_name);
			}
		);

		static::$application_IDs = array_combine($application_IDs, $application_IDs);
		static::$applications_metadata = System_Components::getComponentsList(static::SYSTEM_COMPONENTS_TYPE);

		register_shutdown_function(array(static::class, "end"));
		static::$initialized = true;
	}


	public static function getApplicationByURL($URL = null){
		if(!$URL){
			$URL = ET_REQUEST_URL_WITHOUT_QUERY;
		}

	}


	/**
	 * @param string $module_ID
	 * @throws Application_Exception
	 */
	public static function checkApplicationIDFormat($module_ID){
		if(!preg_match('~^\w+$~', $module_ID)){
			throw new Application_Exception(
				"Invalid application ID format of '{$module_ID}'",
				Application_Exception::CODE_INVALID_APPLICATION_ID
			);
		}
	}


	/**
	 * @param string $application_ID
	 * @return Application_Config
	 * @throws Application_Exception
	 */
	public static function getApplicationConfig($application_ID){
		return self::getApplicationMetadata($application_ID)->getConfig();
	}

	/**
	 * @param string $application_ID
	 * @return Application_Installer
	 * @throws Application_Exception
	 */
	public static function getApplicationInstaller($application_ID){
		return static::getApplicationMetadata($application_ID)->getInstaller();
	}

	/**
	 * @param string $application_ID
	 * @param bool $enabled_only [optional]
	 * @return Application_Abstract
	 * @throws Application_Exception
	 */
	public static function getApplicationInstance($application_ID, $enabled_only = true){

		$metadata = self::getApplicationMetadata($application_ID);
		$enabled = $metadata->isEnabled();
		if(!$enabled && $enabled_only){
			throw new Application_Exception(
				"Application '{$application_ID}' is not enabled",
				Application_Exception::CODE_APPLICATION_NOT_ENABLED
			);
		}

		if(isset(self::$application_instances[$application_ID])){
			if(!$enabled){
				unset(self::$application_instances[$application_ID]);
			} else {
				return self::$application_instances[$application_ID];
			}
		}


		$application_class = "EtApp\\{$application_ID}\\Application";
		if(!class_exists($application_class) || !is_subclass_of($application_class, 'Et\Application_Abstract', true)){
			throw new Application_Exception(
				"Application '{$application_ID}' main model (class {$application_class}) does not exist or is not subclass of Et\\Application_Abstract",
				Application_Exception::CODE_APPLICATION_NOT_EXIST
			);
		}


		$initialize = $enabled;
		$application_instance = new $application_class($metadata, $initialize);
		if(!$enabled){
			return $application_instance;
		}

		self::$application_instances[$application_ID] = $application_instance;
		return self::$application_instances[$application_ID];
	}

	/**
	 * @param string $application_ID
	 * @return bool
	 */
	public static function getApplicationExists($application_ID){
		return isset(static::$application_IDs[$application_ID]);
	}

	/**
	 * @param string $application_ID
	 * @return bool
	 */
	public static function getApplicationIsInstalled($application_ID){
		return static::getApplicationExists($application_ID) && static::getApplicationMetadata($application_ID)->isInstalled();
	}

	/**
	 * @param string $application_ID
	 * @return bool
	 */
	public static function getApplicationIsEnabled($application_ID){
		return static::getApplicationExists($application_ID) && static::getApplicationMetadata($application_ID)->isEnabled();
	}

	/**
	 * @param string $application_ID
	 * @return bool
	 */
	public static function getApplicationIsOutdated($application_ID){
		return static::getApplicationExists($application_ID) && static::getApplicationMetadata($application_ID)->isOutdated();
	}

	/**
	 * @param string $application_ID
	 */
	public static function reloadApplicationMetadata($application_ID){
		static::getApplicationMetadata($application_ID)->reload();
	}

	public static function reloadApplicationsMetadata(){
		foreach(static::$application_IDs as $application_ID){
			static::reloadApplicationMetadata($application_ID);
		}
	}


	/**
	 * @param string $application_ID
	 * @return Application_Metadata
	 * @throws Application_Exception
	 */
	public static function getApplicationMetadata($application_ID){
		static::checkApplicationExists($application_ID);
		if(!isset(self::$applications_metadata[$application_ID])){
			$metadata = new Application_Metadata($application_ID);
			static::$applications_metadata->addComponent($metadata);
		}
		return self::$applications_metadata[$application_ID];
	}

	/**
	 * @return array
	 */
	public static function getApplicationIDs(){
		return static::$application_IDs;
	}

	/**
	 * @return array
	 */
	public static function getApplicationNames(){
		$names = array();
		foreach(static::$application_IDs as $application_ID){
			$names[$application_ID] = static::getApplicationMetadata($application_ID)->getApplicationName();
		}
		return $names;
	}

	/**
	 * @param string $application_ID
	 * @return string
	 */
	public static function getApplicationName($application_ID){
		return static::getApplicationMetadata($application_ID)->getApplicationName();
	}

	/**
	 * @return Application_Metadata[]|System_Components_List
	 */
	public static function getApplicationsMetadata(){
		foreach(static::$application_IDs as $application_ID){
			if(!isset(static::$applications_metadata[$application_ID])){
				static::getApplicationMetadata($application_ID);
			}
		}
		return static::$applications_metadata;
	}

	/**
	 * @return Application_Metadata[]
	 */
	public static function getInstalledApplicationsMetadata(){
		$output = array();
		foreach(static::$application_IDs as $application_ID){
			$metadata = static::getApplicationMetadata($application_ID);
			if(!$metadata->isInstalled()){
				continue;
			}
			$output[$application_ID] = $metadata;
		}
		return $output;
	}

	/**
	 * @return array
	 */
	public static function getInstalledApplicationIDs(){
		return array_keys(self::getInstalledApplicationsMetadata());
	}

	/**
	 * @return array
	 */
	public static function getInstalledApplicationNames(){
		$labels = array();
		$applications_metadata = self::getInstalledApplicationsMetadata();
		foreach($applications_metadata as $application_ID => $metadata){
			$labels[$application_ID] = $metadata->getApplicationName();
		}
		return $labels;
	}


	/**
	 * @return Application_Metadata[]
	 */
	public static function getEnabledApplicationsMetadata(){
		$output = array();
		foreach(static::$application_IDs as $application_ID){
			$metadata = static::getApplicationMetadata($application_ID);
			if(!$metadata->isEnabled()){
				continue;
			}
			$output[$application_ID] = $metadata;
		}
		return $output;
	}

	/**
	 * @return array
	 */
	public static function getEnabledApplicationIDs(){
		return array_keys(self::getEnabledApplicationsMetadata());
	}


	/**
	 * @return array
	 */
	public static function getEnabledApplicationNames(){
		$labels = array();
		$applications_metadata = self::getEnabledApplicationsMetadata();
		foreach($applications_metadata as $application_ID => $metadata){
			$labels[$application_ID] = $metadata->getApplicationName();
		}
		return $labels;
	}


	/**
	 * @return Application_Metadata[]
	 */
	public static function getOutdatedApplicationsMetadata(){
		$output = array();
		foreach(static::$application_IDs as $application_ID){
			$metadata = static::getApplicationMetadata($application_ID);
			if(!$metadata->isOutdated()){
				continue;
			}
			$output[$application_ID] = $metadata;
		}
		return $output;
	}

	/**
	 * @return array
	 */
	public static function getOutdatedApplicationIDs(){
		return array_keys(self::getOutdatedApplicationsMetadata());
	}


	/**
	 * @return array
	 */
	public static function getOutdatedApplicationNames(){
		$labels = array();
		$applications_metadata = self::getOutdatedApplicationsMetadata();
		foreach($applications_metadata as $application_ID => $metadata){
			$labels[$application_ID] = $metadata->getApplicationName();
		}
		return $labels;
	}

	/**
	 * @param string $application_ID
	 * @throws Application_Exception
	 */
	public static function checkApplicationExists($application_ID){
		if(!self::getApplicationExists($application_ID)){
			throw new Application_Exception(
				"Application '{$application_ID}' does not exist",
				Application_Exception::CODE_APPLICATION_NOT_EXIST
			);
		}
	}

	/**
	 * @param string $application_ID
	 * @throws Application_Exception
	 */
	public static function checkApplicationIsInstalled($application_ID){
		self::checkApplicationExists($application_ID);
		if(!self::getApplicationIsInstalled($application_ID)){
			throw new Application_Exception(
				"Application '{$application_ID}' is not installed",
				Application_Exception::CODE_APPLICATION_NOT_INSTALLED
			);
		}
	}

	/**
	 * @param string $application_ID
	 * @throws Application_Exception
	 */
	public static function checkApplicationIsEnabled($application_ID){
		self::checkApplicationExists($application_ID);
		if(!self::getApplicationIsEnabled($application_ID)){
			throw new Application_Exception(
				"Application '{$application_ID}' is not enabled",
				Application_Exception::CODE_APPLICATION_NOT_ENABLED
			);
		}
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
