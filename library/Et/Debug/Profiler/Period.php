<?php
namespace Et;
class Debug_Profiler_Period implements \JsonSerializable {

	/**
	 * @var string
	 */
	protected $name;

	/**
	 * @var float
	 */
	protected $start_time;

	/**
	 * @var int
	 */
	protected $start_memory;

	/**
	 * @var float
	 */
	protected $end_time;

	/**
	 * @var int
	 */
	protected $end_memory;

	/**
	 * @var array
	 */
	protected $backtrace = array();

	/**
	 * @var bool
	 */
	protected $is_finished = false;

	/**
	 * @param string $name
	 */
	function __construct($name){
		$this->name = trim($name);
		$this->fetchBacktrace();
		$this->start();
	}

	function start(){
		$this->start_memory = memory_get_usage();
		$this->start_time = microtime(true);
		$this->end_time = null;
		$this->end_memory = null;
		$this->is_finished = false;
	}

	function end(){
		$this->end_time = microtime(true);
		$this->end_memory = memory_get_usage();
		$this->is_finished = true;
	}

	/**
	 * @return int
	 */
	public function getEndMemory() {
		return $this->end_memory;
	}

	/**
	 * @return float
	 */
	public function getEndTime() {
		return $this->end_time;
	}

	/**
	 * @return boolean
	 */
	public function isFinished() {
		return $this->is_finished;
	}

	/**
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * @return int
	 */
	public function getStartMemory() {
		return $this->start_memory;
	}

	/**
	 * @return float
	 */
	public function getStartTime() {
		return $this->start_time;
	}

	/**
	 * @return bool|float
	 */
	function getDuration(){
		if(!$this->isFinished()){
			return false;
		}
		return $this->getEndTime() - $this->getStartTime();
	}

	/**
	 * @return bool|int
	 */
	function getMemoryDifference(){
		if(!$this->isFinished()){
			return false;
		}
		return $this->getEndMemory() - $this->getStartMemory();
	}

	function fetchBacktrace(){
		$backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
		$base_dir_len = strlen(ET_BASE_PATH);
		$this->backtrace = array();
		foreach($backtrace as $i => $row){
			if(empty($row["file"])){
				$row["file"] = "?";
			}
			$row["file"] = str_replace(array("\\", DIRECTORY_SEPARATOR), "/", $row["file"]);
			if(substr($row["file"], 0, $base_dir_len) == ET_BASE_PATH){
				$row["file"] = "[root]/" . substr($row["file"], $base_dir_len);
			}
			$line = "#" . ($i+1) . " {$row["file"]}:{$row["line"]}";
			if(!empty($row["function"])){
				if(!empty($row["class"])){

					if(is_a($row["class"], __CLASS__, true) || is_subclass_of($row["class"], "Et\\Debug_Profiler_Abstract", true)){
						continue;
					}

					$line .= " | {$row["class"]}{$row["type"]}{$row["function"]}( ... )";
				} else {
					$line .= " | {$row["function"]}( ... )";
				}
			}
			$this->backtrace[] = $line;
		}
	}

	/**
	 * @return array
	 */
	public function getBacktrace() {
		return $this->backtrace;
	}



	/**
	 * @return array
	 */
	function toArray(){
		return array(
			"name" => $this->getName(),
			"is_finished" => $this->isFinished(),
			"duration" => $this->getDuration(),
			"memory_difference" => $this->getMemoryDifference(),
			"backtrace" => $this->getBacktrace()
		);
 	}

	/**
	 * @return array
	 */
	public function jsonSerialize() {
		return $this->toArray();
	}
}