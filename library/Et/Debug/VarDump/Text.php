<?php
namespace Et;
et_require('Debug_VarDump_Abstract');
class Debug_VarDump_Text extends Debug_VarDump_Abstract {


	/**
	 * @param mixed $variable
	 * @return string
	 */
	public function getDump($variable) {
		return $this->getVarDump($variable);
	}


	/**
	 * @param mixed $variable [reference]
	 * @param int $current_depth [optional]
	 * @param array $objects_dumped [reference]
	 *
	 * @return string
	 */
	protected function getVarDump(&$variable, $current_depth = 0, array &$objects_dumped = array()){
		$type = gettype($variable);
		switch($type){

			case static::VAR_TYPE_BOOL:
			case static::VAR_TYPE_INT:
			case static::VAR_TYPE_FLOAT:
			case static::VAR_TYPE_NULL:
				return strtoupper(json_encode($variable));

			case static::VAR_TYPE_STRING:
				if(preg_match('~^(SELECT|INSERT|UPDATE|REPLACE|DELETE|CREATE)\s+~is', ltrim($variable))){
					return '"' . trim(str_replace('"', '\"', $variable)) .  '"';
				}

				if($this->max_text_length && strlen($variable) > $this->max_text_length){
					$variable = substr($variable, 0, $this->max_text_length - 3) . "...";
				}

				return '"' . str_replace('"', '\"', $variable) . '"';

			case static::VAR_TYPE_OBJECT:
				return $this->getObjectDump($variable, $current_depth, $objects_dumped);

			case static::VAR_TYPE_ARRAY:
				return $this->getArrayDump($variable, $current_depth, $objects_dumped);

			default:
				return print_r($variable, true);
		}
	}


	/**
	 * @param object $object [reference]
	 * @param int $current_depth
	 * @param array $objects_dumped [reference]
	 * @return string
	 */
	protected function getObjectDump(&$object, $current_depth, array &$objects_dumped){
		$object_ID = $this->getObjectID($object);
		if(isset($objects_dumped[$object_ID])){
			return $objects_dumped[$object_ID];
		}
		$objects_dumped[$object_ID] = "Object::{$object_ID}";

		$properties = (array)$object;
		if(!$properties){
			return "Object::{$object_ID}{}";
		}

		if($current_depth > $this->max_depth){
			return "Object::{$object_ID}";
		}

		$output = "Object::{$object_ID}{\n";

		$lines = array();

		$class = get_class($object);
		foreach($properties as $property => $value){

			$k = str_replace("\0", "|", $property);
			// private or protected
			if($k[0] == "|"){
				// protected
				if($k[1] == "*"){
					$k = "protected:\$" . substr($k, 3);
				} else {
					$k = "private:\$" . substr($k, strlen("|{$class}|"));
				}
			} else {
				$k = "\${$k}";
			}

			$string_value = $this->getVarDump($value, $current_depth + 1, $objects_dumped);
			$line = "{$k} => {$string_value}";
			$line = "    " . str_replace("\n", "\n    ", $line);
			$lines[] = $line;
		}
		$output .= implode(",\n", $lines) . "\n)";

		return $output;

	}

	/**
	 * @param array $array [reference]
	 * @param int $current_depth
	 * @param array $objects_dumped [reference]
	 * @return string
	 */
	protected function getArrayDump(array &$array, $current_depth, array &$objects_dumped){
		if(!$array){
			return "Array()";
		}

		if($current_depth > $this->max_depth){
			return "Array(" . count($array) . " elements)";
		}

		$output = "Array(\n";
		$lines = array();
		foreach($array as $key => $value){
			$string_value = $this->getVarDump($value, $current_depth + 1, $objects_dumped);

			$line = $this->getVarDump($key, 0) . " => {$string_value}";
			$line = "    " . str_replace("\n", "\n    ", $line);
			$lines[] = $line;
		}
		$output .= implode(",\n", $lines) . "\n)";
		return $output;

	}
}