<?php
namespace Et;
class Loader {

	/**
	 * @var bool
	 */
	protected static $registered = false;

	/**
	 * @var bool
	 */
	protected static $enabled = false;

	/**
	 * @var Loader_Abstract[]
	 */
	protected static $registered_loaders = array();

	/**
	 * @var array
	 */
	protected static $loaded_classes = array();

	/**
	 * @var Loader_Cache_Abstract
	 */
	protected static $cache;

	/**
	 * @var array
	 */
	protected static $cached_paths;

	/**
	 * @var bool
	 */
	protected static $paths_changed = false;


	protected static function register(){
		spl_autoload_register(array(get_called_class(), "loadClass"), true, false);
		register_shutdown_function(function(){
			if(static::$paths_changed){
				static::getCache()->storePaths(static::getCachedPaths());
			}
		});
		static::$registered = true;
	}

	/**
	 * @param \Et\Loader_Cache_Abstract $cache
	 */
	public static function setCache(Loader_Cache_Abstract $cache) {
		static::$cache = $cache;
	}

	/**
	 * @return \Et\Loader_Cache_Abstract
	 */
	public static function getCache() {
		if(!static::$cache){
			et_require("Loader_Cache_Default");
			static::$cache = new Loader_Cache_Default();
		}
		return static::$cache;
	}

	/**
	 * @param bool $force_refresh [optional]
	 * @return array
	 */
	public static function getCachedPaths($force_refresh = false){
		if(static::$cached_paths !== null && !$force_refresh){
			return static::$cached_paths;
		}
		static::$cached_paths = static::getCache()->loadsPaths();
		return static::$cached_paths;
	}



	public static function enable(){
		if(!static::$registered){
			static::register();
		}
		static::$enabled = true;
	}

	public static function disable(){
		static::$enabled = false;
	}

	/**
	 * @return bool
	 */
	public static function isEnabled(){
		return static::$enabled;
	}

	/**
	 * @param string $class_name
	 */
	public static function loadClass($class_name){

		if(static::$cached_paths === null){
			static::getCachedPaths();
		}

		if(isset(static::$cached_paths[$class_name])){
			/** @noinspection PhpIncludeInspection */
			require_once(static::$cached_paths[$class_name]);
			return;
		}

		foreach(static::$registered_loaders as $loader_name => $loader){
			$loaded = $loader->loadClass($class_name);
			if($loaded){
				static::$loaded_classes[$class_name] = array(
					"loader_name" => $loader_name,
					"path" => $loaded
				);
				static::$cached_paths[$class_name] = $loaded;
				static::$paths_changed = true;
			}
		}
	}

	/**
	 * @return array
	 */
	public static function getLoadedClasses(){
		return static::$loaded_classes;
	}

	/**
	 * @return int
	 */
	public static function getLoadedClassesCount(){
		return count(static::$loaded_classes);
	}

	/**
	 * @return Loader_Abstract[]
	 */
	public static function getRegisteredLoaders(){
		return static::$registered_loaders;
	}

	/**
	 * @param Loader_Abstract $loader
	 */
	public static function registerLoader(Loader_Abstract $loader){
		static::$registered_loaders[$loader->getLoaderName()] = $loader;
	}

	/**
	 * @param string $loader_name
	 *
	 * @return bool
	 */
	public static function getLoaderIsRegistered($loader_name){
		return isset(static::$registered_loaders[$loader_name]);
	}

	/**
	 * @param string $loader_name
	 *
	 * @return bool
	 */
	public static function removeRegisteredLoader($loader_name){
		if(!isset(static::$registered_loaders[$loader_name])){
			return false;
		}
		unset(static::$registered_loaders[$loader_name]);
		return true;
	}

	/**
	 * @param string $loader_name
	 *
	 * @return bool|Loader_Abstract
	 */
	public static function getRegisteredLoader($loader_name){
		if(!isset(static::$registered_loaders[$loader_name])){
			return false;
		}
		return static::$registered_loaders[$loader_name];
	}


	/**
	 * Convert class name to path name like
	 * My_Example_Class -> My/Example/Class.php or
	 * \My\Example\Class_Name -> My/Example/Class/Name.php
	 *
	 * @param string $class_name
	 *
	 * @return string
	 */
	public static function convertClassNameToPathName($class_name){
		$path = str_replace(array("\\", "_"), "/", $class_name);
		return trim($path, '/') . ".php";
	}

	/**
	 * @param string $class_name
	 *
	 * @return string
	 */
	public static function getClassNamePrefix($class_name){
		if(preg_match('~^\\?([\w^_]+)~', $class_name, $m)){
			return $m[1];
		}
		return $class_name;
	}

	/**
	 * @param string $class_name
	 *
	 * @throws Loader_Exception
	 */
	public static function checkClassExists($class_name){
		if(!class_exists($class_name)){
			et_require("Loader_Exception");
			throw new Loader_Exception(
				"Class '{$class_name}' not found",
				Loader_Exception::CODE_NOT_EXISTS
			);
		}
	}

	/**
	 * @param string $interface_name
	 *
	 * @throws Loader_Exception
	 */
	public static function checkInterfaceExists($interface_name){
		if(!interface_exists($interface_name)){
			et_require("Loader_Exception");
			throw new Loader_Exception(
				"Interface '{$interface_name}' not found",
				Loader_Exception::CODE_NOT_EXISTS
			);
		}
	}

	/**
	 * @param string $trait_name
	 *
	 * @throws Loader_Exception
	 */
	public static function checkTraitExists($trait_name){
		if(!trait_exists($trait_name)){
			et_require("Loader_Exception");
			throw new Loader_Exception(
				"Trait '{$trait_name}' not found",
				Loader_Exception::CODE_NOT_EXISTS
			);
		}
	}

	/**
	 * @param string $class_name
	 * @param string $parent_class_name
	 *
	 * @throws Loader_Exception
	 */
	public static function checkSubclass($class_name, $parent_class_name){
		self::checkClassExists($class_name);
		self::checkClassExists($parent_class_name);
		if(!is_subclass_of($class_name, $parent_class_name)){
			et_require("Loader_Exception");
			throw new Loader_Exception(
				"Class '{$class_name}' is not subclass of '{$parent_class_name}'",
				Loader_Exception::CODE_NOT_SUBCLASS
			);
		}
	}

	/**
	 * @param string $class_name
	 * @param string $interface_name
	 *
	 * @throws Loader_Exception
	 */
	public static function checkClassInterface($class_name, $interface_name){
		self::checkClassExists($class_name);
		self::checkInterfaceExists($interface_name);

		if(!is_subclass_of($class_name, $interface_name)){
			et_require("Loader_Exception");
			throw new Loader_Exception(
				"Class '{$class_name}' does not implement interface '{$interface_name}'",
				Loader_Exception::CODE_NOT_SUBCLASS
			);
		}
	}

	/**
	 * @param string $class_name
	 * @param string $parent_class_name
	 *
	 * @throws Loader_Exception
	 */
	public static function checkSubclassOrSameClass($class_name, $parent_class_name){
		if($class_name == $parent_class_name){
			self::checkClassExists($class_name);
			return;
		}
		self::checkSubclass($class_name, $parent_class_name);
	}

}