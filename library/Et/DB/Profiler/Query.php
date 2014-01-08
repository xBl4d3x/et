<?php
namespace Et;
class DB_Profiler_Query extends Debug_Profiler_Period {

	/**
	 * @var float
	 */
	protected $fetch_start_time;

	/**
	 * @var int
	 */
	protected $fetch_start_memory;

	/**
	 * @var float
	 */
	protected $fetch_end_time;

	/**
	 * @var int
	 */
	protected $fetch_end_memory;

	/**
	 * @var int
	 */
	protected $result_rows_count = -1;

	/**
	 * @param string $query
	 */
	function __construct($query){
		parent::__construct($query);
	}

	/**
	 * @return string
	 */
	function getQuery(){
		return $this->getName();
	}

	function fetchStarted(){
		$this->fetch_start_time = microtime(true);
		$this->fetch_start_memory = memory_get_usage();
	}

	function fetchEnded(){
		$this->fetch_end_time = microtime(true);
		$this->fetch_end_memory = memory_get_usage();
	}

	/**
	 * @return int
	 */
	public function getFetchEndMemory() {
		return $this->fetch_end_memory;
	}

	/**
	 * @return float
	 */
	public function getFetchEndTime() {
		return $this->fetch_end_time;
	}

	/**
	 * @return int
	 */
	public function getFetchStartMemory() {
		return $this->fetch_start_memory;
	}

	/**
	 * @return float
	 */
	public function getFetchStartTime() {
		return $this->fetch_start_time;
	}

	/**
	 * @param int $result_rows_count
	 */
	public function setResultRowsCount($result_rows_count) {
		$this->result_rows_count = (int)$result_rows_count;
	}

	/**
	 * @return int
	 */
	public function getResultRowsCount() {
		return $this->result_rows_count;
	}





	/**
	 * @return bool|float
	 */
	function getFetchDuration(){
		if(!$this->fetch_start_time || !$this->fetch_end_time){
			return false;
		}
		return $this->getFetchEndTime() - $this->getFetchStartTime();
	}

	/**
	 * @return bool|int
	 */
	function getFetchMemoryDifference(){
		if(!$this->fetch_start_time || !$this->fetch_end_time){
			return false;
		}
		return $this->getFetchEndMemory() - $this->getFetchStartMemory();
	}

	/**
	 * @return array
	 */
	function toArray(){
		$output = parent::toArray();
		$output += array(
			"fetch_duration" => $this->getDuration(),
			"fetch_memory_difference" => $this->getMemoryDifference(),
			"fetch_rows_count" => $this->getResultRowsCount()
		);
		return $output;
	}
}