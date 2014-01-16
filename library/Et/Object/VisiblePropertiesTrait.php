<?php
namespace Et;
trait Object_VisiblePropertiesTrait {

	/**
	 * Returns object variables names which do not begin with '_'
	 *
	 * @return array
	 */
	protected function _getVisiblePropertiesNames(){
		$props = array_keys(get_object_vars($this));
		$output = array();
		foreach($props as $k){
			if($k[0] == "_"){
				continue;
			}
			$output[] = $k;
		}
		return $output;
	}

	/**
	 * Returns object variables values which names do not begin with '_'
	 *
	 * @return array
	 */
	protected function _getVisiblePropertiesValues(){
		$props = $this->_getVisiblePropertiesNames();
		$output = array();
		foreach($props as $prop){
			$output[$prop] = $this->{$prop};
		}
		return $output;
	}

	/**
	 * Returns class variables names which do not begin with '_'
	 *
	 * @return array
	 */
	protected static function _getVisibleClassPropertiesNames(){
		return array_keys(static::_getVisibleClassPropertiesValues());
	}

	/**
	 * Returns class variables values which names do not begin with '_'
	 *
	 * @return array
	 */
	protected static function _getVisibleClassPropertiesValues(){
		$properties = get_class_vars(static::class);
		$props = array_keys($properties);
		foreach($props as $k){
			if($k[0] == "_"){
				unset($properties[$k]);
			}
		}
		return $properties;
	}

	/**
	 * @param string $property
	 *
	 * @return bool
	 */
	protected static function _hasVisibleClassProperty($property){
		return property_exists(static::class, $property) && $property[0] != "_";
	}

	/**
	 * @param string $property
	 *
	 * @return bool
	 */
	protected function _hasVisibleProperty($property){
		return in_array($property, $this->_getVisiblePropertiesNames());
	}

}