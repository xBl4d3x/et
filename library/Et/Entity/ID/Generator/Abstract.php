<?php
namespace Et;
abstract class Entity_ID_Generator_Abstract extends Object {

	const STRING_ID_LENGTH = 40;

	/**
	 * @var int
	 */
	protected $max_string_ID_retry_count = 30;

	/**
	 * @param int $max_string_ID_retry_count
	 */
	public function setMaxStringIDRetryCount($max_string_ID_retry_count) {
		$this->max_string_ID_retry_count = max(0, (int)$max_string_ID_retry_count);
	}

	/**
	 * @return int
	 */
	public function getMaxStringIDRetryCount() {
		return $this->max_string_ID_retry_count;
	}



	/**
	 * Generate new numeric ID for entity
	 *
	 * @param string|Entity_Abstract $entity_class
	 * @return int
	 */
	function generateNumericID($entity_class){
		$entity_class = Entity::resolveEntityClassName($entity_class);
		$ID = $this->_generateNumericID($entity_class);

		if($entity_class::getIDExists($ID)){
			return $this->generateNumericID($entity_class);
		}

		return $ID;
	}


	/**
	 * @param string|Entity_Abstract $entity_class
	 * @return int
	 */
	abstract protected function _generateNumericID($entity_class);

	/**
	 * Generate new generic string ID for entity
	 *
	 * @param string|Entity_Abstract $entity_class
	 * @return string
	 */
	function generateTextID($entity_class){
		$entity_class = Entity::resolveEntityClassName($entity_class);
		$ID = $this->_generateTextID($entity_class);

		if($entity_class::getIDExists($ID)){
			return $this->generateNumericID($entity_class);
		}

		return $ID;
	}

	/**
	 * @param string|Entity_Abstract $entity_class
	 * @return string
	 */
	abstract protected function _generateTextID($entity_class);

	/**
	 * @param string|Entity_Abstract $entity_class
	 * @param string $input_text
	 * @return string
	 * @throws Entity_ID_Generator_Exception
	 */
	function generateIDFromString($entity_class, $input_text){
		$entity_class = Entity::resolveEntityClassName($entity_class);

		$ID = System::getText((string)$input_text)->removeAccents($input_text);
		$ID = trim(preg_replace('~[^a-z0-9]+~', '-', strtolower($ID)), "-");
		if($ID === ""){
			throw new Entity_ID_Generator_Exception(
				"Cannot generate ID from empty string",
				Entity_ID_Generator_Exception::CODE_FAILED_TO_GENERATE_ID
			);
		}

		$max_length = static::STRING_ID_LENGTH;

		$ID = substr($ID, 0, $max_length);
		if(!$entity_class::getIDExists($ID)){
			return $ID;
		}


		$retry = 0;
		do {
			$retry++;

			$new_ID = substr($ID, 0, $max_length - strlen($retry)) . $retry;
			if(!$entity_class::getIDExists($new_ID)){
				return $new_ID;
			}


		} while($this->max_string_ID_retry_count == 0 || $retry <= $this->max_string_ID_retry_count);

		throw new Entity_ID_Generator_Exception(
			"Failed to generate ID for entity '{$entity_class}' in {$this->max_string_ID_retry_count} retries",
			Entity_ID_Generator_Exception::CODE_FAILED_TO_GENERATE_ID
		);
	}

}