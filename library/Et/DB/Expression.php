<?php
namespace Et;
class DB_Expression extends Object {
	
	/**
	 * Expression
	 *
	 * @var string 
	 */
	protected $expression = "";

	/**
	 * Expression instance
	 *
	 * @param string $expression
	 */
	public function __construct($expression){
		$this->expression = (string)$expression;
	}
	
	/**
	 * Get expression
	 *
	 * @return string 
	 */
	public function __toString(){
		return $this->expression;
	}

	/**
	 * @param string $expression
	 *
	 * @return DB_Expression
	 */
	public static function create($expression){
		return new static($expression);
	}
}