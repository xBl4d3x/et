<?php
namespace Et;
class DB_Query_Builder_DB extends DB_Query_Builder_Abstract {

	/**
	 * @var DB_Adapter_Abstract
	 */
	protected $db_adapter;

	/**
	 * @param DB_Adapter_Abstract $adapter
	 */
	function __construct(DB_Adapter_Abstract $adapter){
		$this->db_adapter = $adapter;
	}

	/**
	 * @return \Et\DB_Adapter_Abstract
	 */
	public function getDbAdapter() {
		return $this->db_adapter;
	}

	/**
	 * @param string $identifier
	 * @return string
	 */
	function quoteIdentifier($identifier) {
		return $this->db_adapter->quoteIdentifier($identifier);
	}

	/**
	 * @param mixed $value
	 * @return string
	 */
	function quoteValue($value) {
		return $this->db_adapter->quote($value);
	}

	/**
	 * @param array $value
	 * @return string
	 */
	function quoteIN(array $value){
		return $this->db_adapter->quoteIN($value);
	}
}