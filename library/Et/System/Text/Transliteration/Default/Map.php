<?php
namespace Et;
et_require("Object");
class System_Text_Transliteration_Default_Map extends Object {
	/**
	 * @var array
	 */
	protected static $default_maps = array(
		"Latin",
		"Symbols",
		"Czech",
		"Greek",
		"Polish",
		"Russian",
		"Ukrainian",
		"Turkish",
		"Latvian",
		"Lithuanian"
	);

	/**
	 * @var System_Text_Transliteration_Default_Map_Abstract[]
	 */
	protected $registered_maps = array();

	function __construct(){
		$this->loadDefaultMaps();
	}

	protected function loadDefaultMaps(){
		foreach(static::$default_maps as $map){
			et_require("System_Text_Transliteration_Default_Map_{$map}");
			$class = "Et\\System_Text_Transliteration_Default_Map_{$map}";
			$map_instance = new $class();
			$this->registerMap($map_instance);
		}
	}

	/**
	 * @param System_Text_Transliteration_Default_Map_Abstract $map
	 *
	 * @return System_Text_Transliteration_Default_Map
	 */
	function registerMap(System_Text_Transliteration_Default_Map_Abstract $map){
		$this->registered_maps[$map->getName()] = $map;
		return $this;
	}

	/**
	 * @param string $map_name
	 * @return System_Text_Transliteration_Default_Map
	 */
	function removeMap($map_name){
		if(isset($this->registered_maps[$map_name])){
			unset($this->registered_maps[$map_name]);
		}
		return $this;
	}

	/**
	 * @param string $map_name
	 *
	 * @return bool
	 */
	function isMapRegistered($map_name){
		return isset($this->registered_maps[$map_name]);
	}

	/**
	 * @param string $text
	 *
	 * @return string
	 */
	function transliterate($text){
		foreach($this->registered_maps as $map){
			$text = $map->transliterateText($text);
		}
		return $text;
	}
}