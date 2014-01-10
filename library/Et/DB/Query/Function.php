<?php
namespace Et;
class DB_Query_Function extends Object {


	/**
	 * @var string
	 */
	protected $function_name;

	/**
	 * @var array|DB_Table_Column[]|DB_Expression[]|DB_Query[]
	 */
	protected $arguments = array();

	/**
	 * @param DB_Query $query
	 * @param string $function_name
	 * @param array|DB_Table_Column[]|DB_Expression[]|DB_Query[] $function_arguments [optional]
	 */
	function __construct(DB_Query $query, $function_name, array $function_arguments = array()){
		Debug_Assert::isVariableName($function_name);
		$this->function_name = $function_name;
		if($function_arguments){
			$this->setArguments($query, $function_arguments);
		}
	}

	/**
	 * @param DB_Query $query
	 * @param array|DB_Table_Column[]|DB_Expression[]|DB_Query[] $function_arguments
	 */
	protected function setArguments(DB_Query $query, array $function_arguments) {
		$this->arguments = array();
		foreach($function_arguments as $arg){
			if($arg instanceof DB_Table_Column){
				$query->addTableToQuery($arg->getTableName());
			}
			$this->arguments[] = $arg;
		}
	}

	/**
	 * @return array|DB_Table_Column[]|DB_Expression[]|DB_Query[]
	 */
	public function getArguments() {
		return $this->arguments;
	}

	/**
	 * @return string
	 */
	public function getFunctionName() {
		return $this->function_name;
	}

	/**
	 * @return bool
	 */
	function hasArguments(){
		return (bool)$this->arguments;
	}
}