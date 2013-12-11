<?php
namespace Et;
class Data {

	/**
	 * @param bool $deep_object_clone [optional]
	 * @return static
	 */
	public function cloneObject($deep_object_clone = true){
		if(!$deep_object_clone){
			return clone($this);
		}
		$serialized = serialize($this);
		return unserialize($serialized);
	}

	/**
	 * @param string $mixed_data
	 * @param bool $deep_object_clone [optional]
	 * @return array|mixed|object
	 */
	public static function cloneMixedData($mixed_data, $deep_object_clone = true){
		if(!is_array($mixed_data) && !is_object($mixed_data)){
			return $mixed_data;
		}
		if($deep_object_clone){
			return unserialize(serialize($mixed_data));
		}
		if(is_object($mixed_data)){
			/** @var $mixed_data object */
			return clone($mixed_data);
		}
		foreach($mixed_data as $k => $v){
			if(!is_array($v) && !is_object($v)){
				continue;
			}
			$mixed_data[$k] = static::cloneMixedData($v, false);
		}
		return $mixed_data;
	}

}