<?php
namespace Et;
trait DB_Query_Compare_Trait {

	/**
	 * @var string
	 */
	protected $compare_operator;

	/**
	 * @var mixed|null|array|DB_Query_Function|DB_Query|DB_Query_Column
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
	 * @param DB_Query $query
	 * @param string $compare_operator
	 * @param mixed|null|array|\Iterator|DB_Query $value
	 * @throws DB_Query_Exception
	 */
	protected function setupValue(DB_Query $query, $compare_operator, $value){

		if($compare_operator == "!="){
			$compare_operator = DB_Query::CMP_NOT_EQUALS;
		}

		$query->checkCompareOperator($compare_operator);
		$this->compare_operator = $compare_operator;

		$this->is_IN_compare = in_array($compare_operator, array(DB_Query::CMP_IN, DB_Query::CMP_NOT_IN));
		$this->is_NULL_compare = in_array($compare_operator, array(DB_Query::CMP_IS_NULL, DB_Query::CMP_IS_NOT_NULL));

		if($value instanceof DB_Query || $value instanceof DB_Expression){
			$this->value = $value;
			return;
		}

		if(!$this->is_IN_compare && $value instanceof DB_Query_Column){
			$this->value = $value;
			return;
		}

		if($value instanceof \Iterator){
			$tmp = array();
			foreach($value as $v){
				$tmp[] = $v;
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
	 * @return array|DB_Query|DB_Query_Column|DB_Query_Function|mixed|null
	 */
	public function getValue() {
		return $this->value;
	}
}