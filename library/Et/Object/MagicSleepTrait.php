<?php
namespace Et;
trait Object_MagicSleepTrait {

	/**
	 * @return array
	 */
	function __sleep(){
		$var_names = array_keys(get_object_vars($this));
		$output = array();
		foreach($var_names as $k){
			if($k[0] == "_" && $k[1] == "_"){
				continue;
			}
			$output[] = $k;
		}
		return $output;
	}

}