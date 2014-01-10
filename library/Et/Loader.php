<?php
namespace Et;

class Loader {

	/**
	 * @var bool
	 */
	protected static $activated = false;

	/**
	 * @var Loader_Abstract[]
	 */
	protected static $registered_loaders = array();


	/**
	 * @var Loader_Cache_Abstract
	 */
	protected static $cache;

	/**
	 * @param bool $enable_cache [optional]
	 */
	public static function activate($enable_cache = true){
		if(static::$activated){
			return;
		}

		spl_autoload_register(
			array(
				static::class, "loadClass"
			),
			true,
			true
		);

		static::$activated = true;


		if(!$enable_cache){
			static::$cache = null;
			return;
		}

		if(!static::$cache){
			et_require("Loader_Cache_Default");
			static::$cache = new Loader_Cache_Default();
		}

		static::$cache->loadsPaths();

		register_shutdown_function(function(){
			if(static::$cache->hasChanged()){
				static::$cache->storePaths();
			}
		});
	}

	/**
	 * @param \Et\Loader_Cache_Abstract $cache
	 */
	public static function setCache(Loader_Cache_Abstract $cache) {
		static::$cache = $cache;
	}

	/**
	 * @return \Et\Loader_Cache_Abstract|null
	 */
	public static function getCache() {
		return static::$cache;
	}


	/**
	 * @param string $class_name
	 */
	public static function loadClass($class_name){

		if(static::$cache){
			$path = static::$cache->getClassPath($class_name);
			if($path){
				/** @noinspection PhpIncludeInspection */
				require_once($path);
				return;
			}
		}

		foreach(static::$registered_loaders as $loader){
			$loaded = $loader->loadClass($class_name);
			if($loaded && static::$cache){
				static::$cache->setClassPath($class_name, $loaded);
			}
		}
	}

	/**
	 * @return \Et\Loader_Abstract[]
	 */
	public static function getRegisteredLoaders(){
		return static::$registered_loaders;
	}

	/**
	 * @param Loader_Abstract $loader
	 * @return string
	 */
	public static function registerLoader(Loader_Abstract $loader){
		$ID = get_class($loader) . ":" . spl_object_hash($loader);
		static::$registered_loaders[$ID] = $loader;
		return $ID;
	}

	/**
	 * @param string $loader_ID
	 *
	 * @return bool
	 */
	public static function getLoaderIsRegistered($loader_ID){
		return isset(static::$registered_loaders[$loader_ID]);
	}

	/**
	 * @param string $loader_ID
	 *
	 * @return bool
	 */
	public static function removeRegisteredLoader($loader_ID){
		if(!isset(static::$registered_loaders[$loader_ID])){
			return false;
		}
		unset(static::$registered_loaders[$loader_ID]);
		return true;
	}

	/**
	 * @param string $loader_ID
	 *
	 * @return bool|\Et\Loader_Abstract
	 */
	public static function getRegisteredLoader($loader_ID){
		if(!isset(static::$registered_loaders[$loader_ID])){
			return false;
		}
		return static::$registered_loaders[$loader_ID];
	}


	/**
	 * @param string $class_name
	 *
	 * @throws \Et\Loader_Exception
	 */
	public static function checkClassExists($class_name){
		if(!class_exists($class_name)){
			throw new Loader_Exception(
				"Class '{$class_name}' not found",
				Loader_Exception::CODE_NOT_EXISTS
			);
		}
	}

	/**
	 * @param string $interface_name
	 *
	 * @throws \Et\Loader_Exception
	 */
	public static function checkInterfaceExists($interface_name){
		if(!interface_exists($interface_name)){
			throw new Loader_Exception(
				"Interface '{$interface_name}' not found",
				Loader_Exception::CODE_NOT_EXISTS
			);
		}
	}

	/**
	 * @param string $trait_name
	 *
	 * @throws \Et\Loader_Exception
	 */
	public static function checkTraitExists($trait_name){
		if(!trait_exists($trait_name)){
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
		if(!is_subclass_of($class_name, $parent_class_name, true)){
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