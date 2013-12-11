<?php
namespace Et;
class Debug_Profiler_Milestone implements \JsonSerializable {

	/**
	 * @var Debug_Profiler_Milestone|null
	 */
	protected $previous_milestone;

	/**
	 * @var Debug_Profiler_Milestone|null
	 */
	protected $next_milestone;

	/**
	 * @var string
	 */
	protected $name;

	/**
	 * @var float
	 */
	protected $time;

	/**
	 * @var int
	 */
	protected $used_memory;

	/**
	 * @param string $name
	 * @param Debug_Profiler_Milestone $previous_milestone [optional]
	 * @param null|float $custom_time [optional]
	 */
	function __construct($name, Debug_Profiler_Milestone $previous_milestone = null, $custom_time = null){
		$this->name = trim($name);
		$this->used_memory = memory_get_usage();
		if($previous_milestone){
			$this->previous_milestone = $previous_milestone;
			$previous_milestone->next_milestone = $this;
		}
		if($custom_time !== null){
			$this->time = (float)$custom_time;
		} else {
			$this->time = microtime(true);
		}
	}

	/**
	 * @return bool
	 */
	function isFirst(){
		return !$this->previous_milestone;
	}

	/**
	 * @return bool
	 */
	function isLast(){
		return !$this->next_milestone;
	}

	/**
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * @return \Et\Debug_Profiler_Milestone|null
	 */
	public function getNextMilestone() {
		return $this->next_milestone;
	}

	/**
	 * @return \Et\Debug_Profiler_Milestone|null
	 */
	public function getPreviousMilestone() {
		return $this->previous_milestone;
	}

	/**
	 * @return float
	 */
	public function getTime() {
		return $this->time;
	}

	/**
	 * @return int
	 */
	public function getUsedMemory() {
		return $this->used_memory;
	}

	/**
	 * @return float
	 */
	public function getDuration(){
		if($this->isFirst()){
			return 0;
		}
		return $this->getTime() - $this->getPreviousMilestone()->getTime();
	}

	/**
	 * @return int
	 */
	public function getMemoryDifference(){
		if($this->isFirst()){
			return 0;
		}
		return $this->getUsedMemory() - $this->getPreviousMilestone()->getUsedMemory();
	}

	/**
	 * @return array
	 */
	public function toArray(){
		return array(
			"name" => $this->getName(),
			"time" => $this->getTime(),
			"used_memory" => $this->getUsedMemory(),
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