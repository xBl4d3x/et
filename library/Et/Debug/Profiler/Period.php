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
	 * @var bool
	 */
	protected $is_finished = false;

	/**
	 * @param string $name
	 */
	function __construct($name){
		$this->name = trim($name);
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

	/**
	 * @return array
	 */
	function toArray(){
		return array(
			"name" => $this->getName(),
			"start_time" => $this->getStartTime(),
			"start_memory" => $this->getStartMemory(),
			"end_time" => $this->getEndTime(),
			"end_memory" => $this->getEndMemory(),
			"is_finished" => $this->isFinished(),
			"duration" => $this->getDuration(),
			"memory_difference" => $this->getMemoryDifference()
		);
 	}

	/**
	 * @return array
	 */
	public function jsonSerialize() {
		return $this->toArray();
	}
}