<?php
namespace Et;

class ClassLoader {

	/**
	 * @var bool
	 */
	protected static $activated = false;

	/**
	 * @var ClassLoader_Abstract[]
	 */
	protected static $registered_loaders = array();


	/**
	 * @var ClassLoader_Cache_Abstract
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
			et_require("ClassLoader_Cache_Default");
			static::$cache = new ClassLoader_Cache_Default();
		}

		static::$cache->loadsPaths();

		register_shutdown_function(function(){
			if(static::$cache->hasChanged()){
				static::$cache->storePaths();
			}
		});
	}

	/**
	 * @param \Et\ClassLoader_Cache_Abstract $cache
	 */
	public static function setCache(ClassLoader_Cache_Abstract $cache) {
		static::$cache = $cache;
	}

	/**
	 * @return \Et\ClassLoader_Cache_Abstract|null
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
	 * @return \Et\ClassLoader_Abstract[]
	 */
	public static function getRegisteredLoaders(){
		return static::$registered_loaders;
	}

	/**
	 * @param ClassLoader_Abstract $loader
	 * @return string
	 */
	public static function registerLoader(ClassLoader_Abstract $loader){
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
	 * @return bool|\Et\ClassLoader_Abstract
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
	 * @throws \Et\ClassLoader_Exception
	 */
	public static function checkClassExists($class_name){
		if(!class_exists($class_name)){
			throw new ClassLoader_Exception(
				"Class '{$class_name}' not found",
				ClassLoader_Exception::CODE_NOT_EXISTS
			);
		}
	}

	/**
	 * @param string $interface_name
	 *
	 * @throws \Et\ClassLoader_Exception
	 */
	public static function checkInterfaceExists($interface_name){
		if(!interface_exists($interface_name)){
			throw new ClassLoader_Exception(
				"Interface '{$interface_name}' not found",
				ClassLoader_Exception::CODE_NOT_EXISTS
			);
		}
	}

	/**
	 * @param string $trait_name
	 *
	 * @throws \Et\ClassLoader_Exception
	 */
	public static function checkTraitExists($trait_name){
		if(!trait_exists($trait_name)){
			throw new ClassLoader_Exception(
				"Trait '{$trait_name}' not found",
				ClassLoader_Exception::CODE_NOT_EXISTS
			);
		}
	}

	/**
	 * @param string $class_name
	 * @param string $parent_class_name
	 *
	 * @throws ClassLoader_Exception
	 */
	public static function checkSubclass($class_name, $parent_class_name){
		self::checkClassExists($class_name);
		self::checkClassExists($parent_class_name);
		if(!is_subclass_of($class_name, $parent_class_name, true)){
			throw new ClassLoader_Exception(
				"Class '{$class_name}' is not subclass of '{$parent_class_name}'",
				ClassLoader_Exception::CODE_NOT_SUBCLASS
			);
		}
	}

	/**
	 * @param string $class_name
	 * @param string $interface_name
	 *
	 * @throws ClassLoader_Exception
	 */
	public static function checkClassInterface($class_name, $interface_name){
		self::checkClassExists($class_name);
		self::checkInterfaceExists($interface_name);

		if(!is_subclass_of($class_name, $interface_name)){
			throw new ClassLoader_Exception(
				"Class '{$class_name}' does not implement interface '{$interface_name}'",
				ClassLoader_Exception::CODE_NOT_SUBCLASS
			);
		}
	}


	/**
	 * @param string $class_name
	 * @param string $parent_class_name
	 *
	 * @throws ClassLoader_Exception
	 */
	public static function checkSubclassOrSameClass($class_name, $parent_class_name){
		if($class_name == $parent_class_name){
			self::checkClassExists($class_name);
			return;
		}
		self::checkSubclass($class_name, $parent_class_name);
	}

}