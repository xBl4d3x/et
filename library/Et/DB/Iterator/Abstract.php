<?php
namespace Et;
abstract class DB_Iterator_Abstract extends Object implements \Iterator, \Countable {

	/**
	 * @var object|resource
	 */
	protected $result;

	/**
	 * @var string
	 */
	protected $fetch_type;

	/**
	 * @var int
	 */
	protected $rows_count;

	/**
	 * @var int
	 */
	protected $columns_count;

	/**
	 * @var bool
	 */
	protected $cache_results = false;

	/**
	 * @var array
	 */
	protected $cached_results = array();

	/**
	 * @var array
	 */
	protected $current_row;

	/**
	 * @var int
	 */
	protected $iterator_position;

	/**
	 * @param DB_Adapter_Abstract $adapter
	 * @param object|resource $result
	 * @param string $fetch_type [optional]
	 * @param bool $cache_results [optional]
	 */
	function __construct(DB_Adapter_Abstract $adapter, $result, $fetch_type = DB::FETCH_ASSOCIATIVE, $cache_results = false){
		$this->result = $result;
		$this->fetch_type = $fetch_type == DB::FETCH_VALUES
							? DB::FETCH_VALUES
							: DB::FETCH_ASSOCIATIVE;
		$this->fetchCounts();
		$this->cache_results = (bool)$cache_results;
	}

	abstract protected function fetchCounts();

	function __destruct(){
		if($this->result){
			$this->freeResult();
		}
	}

	/**
	 * @return string
	 */
	public function getFetchType() {
		return $this->fetch_type;
	}

	/**
	 * @return int
	 */
	public function getRowsCount() {
		return $this->rows_count;
	}

	/**
	 * @return int
	 */
	public function getColumnsCount() {
		return $this->columns_count;
	}


	function freeResult(){
		if(!$this->result){
			return;
		}
		$this->_freeResult();
		$this->result = null;
	}

	abstract protected function _freeResult();


	/**
	 * @return array|bool
	 */
	public function current() {
		return $this->current_row;
	}

	public function next() {
		$this->fetchRow();
	}

	/**
	 * @return int|null
	 */
	public function key() {
		return $this->iterator_position;
	}

	/**
	 * @return bool
	 */
	public function valid() {
		return $this->iterator_position !== null;
	}


	public function rewind() {
		$this->iterator_position = null;
		$this->fetchRow();
	}

	/**
	 * @return array|bool
	 */
	function fetchRow(){
		$next_iterator_position = $this->iterator_position === null
								? 0
								: $this->iterator_position + 1;

		if($this->cached_results && isset($this->cached_results[$next_iterator_position])){
			$this->current_row = $this->cached_results[$next_iterator_position];
			$this->iterator_position = $next_iterator_position;
			return $this->current_row;
		}

		$row = $this->fetch();
		if($row === false){
			$this->current_row = false;
			$this->iterator_position = null;
			return false;
		}

		$this->current_row = $row;
		$this->iterator_position = $next_iterator_position;

		if($this->cache_results){
			$this->cached_results[$next_iterator_position] = $row;
		}

		return $row;
	}

	abstract protected function fetch();

	/**
	 * @return int
	 */
	public function count() {
		return $this->getRowsCount();
	}
}