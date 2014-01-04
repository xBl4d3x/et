<?php
namespace Et;
class Entity_ID_Generator_Default extends Entity_ID_Generator_Abstract {

	/**
	 * @var int
	 */
	protected $max_lock_retry = 5;

	/**
	 * @var \Et\System_Dir
	 */
	protected $counters_directory;

	/**
	 * @param string|\Et\System_Dir $counters_directory
	 */
	public function setCountersDirectory($counters_directory) {
		if(!$counters_directory instanceof System_Dir){
			$counters_directory = System::getDir((string)$counters_directory);
		}
		$this->counters_directory = $counters_directory;
	}

	/**
	 * @return \Et\System_Dir
	 */
	public function getCountersDirectory() {
		if(!$this->counters_directory){
			$this->setCountersDirectory(ET_SYSTEM_DATA_PATH . "entity_ID_counters");
		}
		return $this->counters_directory;
	}

	/**
	 * @param int $max_lock_retry
	 */
	public function setMaxLockRetry($max_lock_retry) {
		$this->max_lock_retry = max(0, (int)$max_lock_retry);
	}

	/**
	 * @return int
	 */
	public function getMaxLockRetry() {
		return $this->max_lock_retry;
	}


	/**
	 * @param string|Entity_Abstract $entity_class
	 * @throws Entity_ID_Generator_Exception
	 * @return int
	 */
	protected function _generateNumericID($entity_class) {
		$entity_name = $entity_class::getEntityName();
		$fn = (string)$this->getCountersDirectory() . "{$entity_name}.counter";
		if(!file_exists($fn)){
			System::getFile($fn)->writeContent("0", true);
		}


		$fp = @fopen($fn, "r+");
		if(!$fp){
			throw new Entity_ID_Generator_Exception(
				"Failed to open file '{$fn}' with last ID for entity '{$entity_class}'",
				Entity_ID_Generator_Exception::CODE_FAILED_TO_GENERATE_ID
			);
		}

		$retry = 0;
		do {

			$retry++;

			if(@flock($fp, LOCK_EX)){
				$ID = (int)fgets($fp) + 1;
				@fseek($fp, 0);
				if(!@fputs($fp, (string)$ID)){
					throw new Entity_ID_Generator_Exception(
						"Failed write entity '{$entity_class}' counter value to '{$fn}'",
						Entity_ID_Generator_Exception::CODE_FAILED_TO_GENERATE_ID
					);
				}
				@flock($fp, LOCK_UN);
				@fclose($fp);
				return $ID;
			}


			usleep(10000);
		} while(!$this->max_lock_retry || $retry <= $this->max_lock_retry);

		@fclose($fp);

		throw new Entity_ID_Generator_Exception(
			"Failed to generate ID for entity '{$entity_class}' in {$this->max_lock_retry} retries",
			Entity_ID_Generator_Exception::CODE_FAILED_TO_GENERATE_ID
		);

	}

	/**
	 * @param string|Entity_Abstract $entity_class
	 * @param bool $short
	 * @return int
	 */
	protected function _generateTextID($entity_class, $short) {
		$ID = uniqid(php_uname('n') . "-" . microtime(true) . "-" . mt_rand(1000000, 9999999), true);
		return $short
				? md5($ID)
				: sha1($ID);
	}
}