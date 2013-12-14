<?php
namespace Et;
trait DB_Query_Where_CompareTrait {

	/**
	 * @var string
	 */
	protected $compare_operator;

	/**
	 * @var mixed|null|array|\Iterator|DB_Query|DB_Query_Column
	 */
	protected $value;

	/**
	 * @var bool
	 */
	protected $is_IN_compare = false;

	/**
	 * @var bool
	 */
	protected $is_NULL_compare = false;


	/**
	 * @param string $compare_operator
	 * @param mixed|null|array|\Iterator|DB_Query $value
	 * @throws DB_Query_Exception
	 */
	protected function setupValue($compare_operator, $value){
		DB_Query_Where::checkCompareOperator($compare_operator);
		$this->compare_operator = $compare_operator;

		$this->is_IN_compare = in_array($compare_operator, array(DB_Query_Where::CMP_IN, DB_Query_Where::CMP_NOT_IN));
		$this->is_NULL_compare = in_array($compare_operator, array(DB_Query_Where::CMP_IS_NULL, DB_Query_Where::CMP_IS_NOT_NULL));

		if($value instanceof DB_Query){
			$this->value = $value;
			return;
		}

		if(!$this->is_IN_compare && ($value instanceof DB_Query_Column || $value instanceof DB_Table_Column_Definition)){
			$this->value = $value;
			return;
		}

		if($value instanceof \Iterator || is_array($value)){
			$tmp = array();
			foreach($value as $k => $v){
				if(!is_scalar($v) && !is_null($v) && !$value instanceof DB_Expression){
					$v = (string)$v;
				}
				$tmp[$k] = $v;
			}
			$value = $tmp;
		}

		if(is_array($value) && !$this->is_IN_compare){
			throw new DB_Query_Exception(
				"Array/Iterator value is available only for IN/NOT IN operator",
				DB_Query_Exception::CODE_INVALID_VALUE
			);
		}

		if($this->is_IN_compare){

			if(!is_array($value)){
				throw new DB_Query_Exception(
					"IN/NOT IN operator requires array value, Iterator or sub query",
					DB_Query_Exception::CODE_INVALID_VALUE
				);
			}

			if(!$value){
				throw new DB_Query_Exception(
					"At least 1 value in array is required for IN/NOT IN operator",
					DB_Query_Exception::CODE_INVALID_VALUE
				);
			}
		}

		$this->value = $value;
	}

	/**
	 * @return string
	 */
	public function getCompareOperator() {
		return $this->compare_operator;
	}

	/**
	 * @return boolean
	 */
	public function isINCompare() {
		return $this->is_IN_compare;
	}

	/**
	 * @return boolean
	 */
	public function isNULLCompare() {
		return $this->is_NULL_compare;
	}

	/**
	 * @return array|DB_Query|\Iterator|DB_Query_Column|mixed|null
	 */
	public function getValue() {
		return $this->value;
	}

	/**
	 * @param DB_Adapter_Abstract $db
	 * @return string
	 */
	protected function getComparePartAsSQL(DB_Adapter_Abstract $db){

		$operator = $this->getCompareOperator();
		$output = $operator;

		if($this->isNULLCompare()){
			return $output;
		}


		$value = $this->getValue();
		if($value instanceof DB_Query){
			return $output . " (\n" . str_replace("\n", "\n\t", $value->buildQuery($db)) . "\n)";
		}

		if($value instanceof DB_Query_Column){
			return "{$output} {$value->toSQL($db)}";
		}

		if($value instanceof DB_Table_Column){
			return "{$output} " . $db->quoteColumnName($value->getColumnName(true));
		}

		if(!$this->is_IN_compare){
			return "{$output} {$db->quoteValue($value)}";
		}

		return "{$output} (" .$db->quoteIN($value) . ")";
	}

	/**
	 * @return string
	 */
	protected function getComparePartAsString(){
		$operator = $this->getCompareOperator();
		$output = $operator;

		if($this->isNULLCompare()){
			return $output;
		}


		$value = $this->getValue();
		if($value instanceof DB_Query){
			return $output . " (\n" . str_replace("\n", "\n\t", (string)$value) . "\n)";
		}


		if($value instanceof DB_Expression){
			return $output . " " . (string)$value;
		}

		if( $value instanceof DB_Query_Column){
			return $output . " " . $value->getColumnName(true);
		}

		if(!$this->is_IN_compare){
			if($value === NULL){
				return $output . " NULL";
			}

			if(is_string($value) || is_object($value)){
				return $output . " '" . addslashes((string)$value) . "'";
			}

			if(is_bool($value)){
				$value = (int)$value;
			}

			return $output ." {$value}";
		}

		$tmp = array();
		foreach($this->value as $v){
			if($v === null){
				$tmp[]= "NULL";
				continue;
			}

			if($v instanceof DB_Expression){
				$tmp[] = (string)$v;
				continue;
			}

			if(is_string($value) || is_object($value)){
				$tmp[]= "'" . addslashes((string)$value) . "'";
				continue;
			}

			if(is_bool($value)){
				$tmp[] = (int)$value;
				continue;
			}

			$tmp[] = $value;
		}

		return $output . " (\n\t" . implode(",\n\t", $tmp) . "\n)";

	}



}