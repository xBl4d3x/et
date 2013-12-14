<?php
namespace Et;
class DB_Iterator_PDO extends DB_Iterator_Abstract {

	/**
	 * @var \PDOStatement
	 */
	protected $result;


	/**
	 * @param DB_Adapter_PDO $adapter
	 * @param \PDOStatement $result
	 * @param string $fetch_type [optional]
	 * @param bool $cache_results [optional]
	 */
	function __construct(DB_Adapter_PDO $adapter, \PDOStatement $result, $fetch_type = DB::FETCH_ASSOCIATIVE, $cache_results = false){
		parent::__construct($adapter, $result, $fetch_type, $cache_results);
	}


	protected function fetchCounts() {
		$this->rows_count = $this->result->rowCount();
		$this->columns_count = $this->result->columnCount();
	}

	protected function _freeResult() {
		$this->result->closeCursor();
	}

	/**
	 * @return bool|array
	 */
	protected function fetch() {
		if($this->result === null){
			return false;
		}
		return $this->result->fetch(
					$this->fetch_type == DB::FETCH_VALUES
						? \PDO::FETCH_NUM
						: \PDO::FETCH_ASSOC
		);
	}
}