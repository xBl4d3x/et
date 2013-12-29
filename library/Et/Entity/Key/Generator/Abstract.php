<?php
namespace Et;
abstract class Entity_Key_Generator_Abstract extends Object {

	/**
	 * @var int
	 */
	protected $max_ID_from_string_retry = 30;

	/**
	 * @param int $max_ID_from_string_retry
	 */
	public function setMaxIDFromStringRetry($max_ID_from_string_retry) {
		$this->max_ID_from_string_retry = $max_ID_from_string_retry;
	}

	/**
	 * @return int
	 */
	public function getMaxIDFromStringRetry() {
		return $this->max_ID_from_string_retry;
	}



	/**
	 * Generate new numeric ID for entity
	 *
	 * @param string|Entity_Abstract $entity_class
	 * @param callable $check_if_exists_callback [optional] If not set, ::getIDExists() entity method is used
	 * @param array $additional_check_arguments [optional]
	 * @return int
	 */
	function generateNumericID($entity_class, callable $check_if_exists_callback = null, array $additional_check_arguments = array()){
		$entity_class = Entity::resolveEntityClassName($entity_class);
		$ID = $this->_generateNumericID($entity_class);

		if(!$check_if_exists_callback){
			$check_if_exists_callback = array($entity_class, "getIDExists");
		}

		$arguments = array($ID, $entity_class);
		foreach($additional_check_arguments as $arg){
			$arguments[] = $arg;
		}

		if(call_user_func_array($check_if_exists_callback, $arguments)){
			return $this->generateNumericID($entity_class, $check_if_exists_callback, $additional_check_arguments);
		}

		return $ID;
	}


	/**
	 * @param string|Entity_Abstract $entity_class
	 * @return int
	 */
	abstract protected function _generateNumericID($entity_class);

	/**
	 * Generate new string ID for entity
	 *
	 * @param string|Entity_Abstract $entity_class
	 * @param bool $short [optional] Generate 32 characters ID instead of 40?
	 * @param callable $check_if_exists_callback [optional]  If not set, ::getIDExists() entity method is used
	 * @param array $additional_check_arguments [optional]
	 * @return string
	 */
	function generateTextID($entity_class, $short = false, callable $check_if_exists_callback = null, array $additional_check_arguments = array()){
		$entity_class = Entity::resolveEntityClassName($entity_class);
		$ID = $this->_generateTextID($entity_class, $short);

		if(!$check_if_exists_callback){
			$check_if_exists_callback = array($entity_class, "getIDExists");
		}

		$arguments = array($ID, $entity_class);
		foreach($additional_check_arguments as $arg){
			$arguments[] = $arg;
		}

		if(call_user_func_array($check_if_exists_callback, $arguments)){
			return $this->generateTextID($entity_class, $short, $check_if_exists_callback, $additional_check_arguments);
		}

		return $ID;
	}

	/**
	 * @param string|Entity_Abstract $entity_class
	 * @param bool $short
	 * @return int
	 */
	abstract protected function _generateTextID($entity_class, $short);

	/**
	 * @param string|Entity_Abstract $entity_class
	 * @param string $input_text
	 * @param int $max_length [optional]
	 * @param callable $check_if_exists_callback [optional]  If not set, ::getIDExists() entity method is used
	 * @param array $additional_check_arguments [optional]
	 * @return bool|string FALSE when transliterated input text is empty
	 * @throws Entity_Key_Generator_Exception
	 */
	function generateIDFromString($entity_class, $input_text, $max_length = 40, callable $check_if_exists_callback = null, array $additional_check_arguments = array()){
		$entity_class = Entity::resolveEntityClassName($entity_class);
		$ID = System::getText((string)$input_text)->removeAccents($input_text);
		$ID = trim(preg_replace('~[^a-z0-9]+~', '-', strtolower($ID)), "-");
		if($ID === ""){
			return false;
		}

		$ID = substr($ID, 0, $max_length);
		if(!$check_if_exists_callback){
			$check_if_exists_callback = array($entity_class, "getIDExists");
		}

		$arguments = array($ID, $entity_class);
		foreach($additional_check_arguments as $arg){
			$arguments[] = $arg;
		}

		if(!call_user_func_array($check_if_exists_callback, $arguments)){
			return $ID;
		}


		$retry = 0;
		do {
			$retry++;

			$arguments[0] = substr($ID, 0, $max_length - strlen($retry)) . $retry;
			if(!call_user_func_array($check_if_exists_callback, $arguments)){
				return $arguments[0];
			}


		} while($this->max_ID_from_string_retry == 0 || $retry <= $this->max_ID_from_string_retry);

		throw new Entity_Key_Generator_Exception(
			"Failed to generate ID for entity '{$entity_class}' in {$this->max_ID_from_string_retry} retries",
			Entity_Key_Generator_Exception::CODE_FAILED_TO_GENERATE_ID
		);
	}

}