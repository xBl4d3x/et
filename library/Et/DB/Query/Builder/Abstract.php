<?php
namespace Et;
abstract class DB_Query_Builder_Abstract extends Object {

	/**
	 * @param string $identifier
	 * @return string
	 */
	abstract function quoteIdentifier($identifier);

	/**
	 * @param mixed $value
	 * @return string
	 */
	abstract function quoteValue($value);

	/**
	 * @param array $value
	 * @return string
	 */
	function quoteIN(array $value){
		foreach($value as &$v){
			$v = $this->quoteValue($v);
		}
		return implode(",\n", $value);
	}

	/**
	 * @param DB_Query $query
	 * @return string
	 */
	function buildQuery(DB_Query $query){

		$output = "SELECT\n";
		$output .= "\t" . str_replace("\n", "\n\t", $this->buildSelectExpression($query->getSelect())) . "\n";

		$output .= "FROM\n";
		$output .= "\t" . str_replace("\n", "\n\t", $this->buildFromExpression($query)) . "\n";

		if(!$query->getWhere()->isEmpty()){
			$output .= "WHERE\n";
			$output .= "\t" . str_replace("\n", "\n\t", $this->buildCompareExpression($query->getWhere())) . "\n";
		}

		$group_by = $query->getGroupBy();
		if(!$group_by->isEmpty()){
			$output .= "GROUP BY\n";
			$output .= "\t" . str_replace("\n", "\n\t", $this->buildGroupByExpression($group_by)) . "\n";
		}

		$having = $query->getHaving();
		if(!$having->isEmpty()){
			$output .= "HAVING\n";
			$output .= "\t" . str_replace("\n", "\n\t", $this->buildCompareExpression($having)) . "\n";
		}

		$order_by = $query->getOrderBy();
		if(!$order_by->isEmpty()){
			$output .= "ORDER BY\n";
			$output .= "\t" . str_replace("\n", "\n\t", $this->buildOrderByExpression($order_by)) . "\n";
		}

		if($query->getLimit() > 0){
			$output .= "LIMIT {$query->getLimit()}\n";
		}

		if($query->getOffset() > 0){
			$output .= "OFFSET {$query->getOffset()}\n";
		}

		return rtrim($output);
	}


	/**
	 * @param DB_Query_Select $select
	 * @return string
	 */
	public function buildSelectExpression(DB_Query_Select $select){

		if($select->isEmpty()){
			return "*";
		}

		$statements = $select->getStatements();
		$query = $select->getQuery();

		$skip_main_table = $query->getTablesInQueryCount() == 1;
		$main_table_name = $query->getMainTableName();

		$output = array();
		foreach($statements as $statement){

			// SELECT * | table_name.*
			if($statement instanceof DB_Query_Select_AllColumns){

				$statement_table = $statement->getTableName();
				if(!$statement_table || ($skip_main_table && $statement_table == $main_table_name)){

					$output[] = "*";

				} else {

					$output[] = "{$this->quoteIdentifier($statement_table)}.*";

				}

				continue;
			}

			$select_as = "";
			if($statement->getSelectAs()){
				$select_as = " AS {$this->quoteIdentifier($statement->getSelectAs())}";
			}


			// SELECT column_name | table.column_name
			if($statement instanceof DB_Query_Select_Column){

				$skip_name = $skip_main_table && $statement->getTableName() == $main_table_name;
				// column select
				$output[] =  $this->quoteIdentifier($statement->getColumnName(!$skip_name)) . $select_as;
				continue;

			}

			// SELECT (SELECT something FROM somewhere) AS smtg
			if($statement instanceof DB_Query_Select_Query){

				// subquery select
				$output[] = "(\n\t" . str_replace("\n", "\n\t", $this->buildQuery($statement->getSubQuery())) . "\n){$select_as}";
				continue;

			}


			// SELECT SOME_FUNCTION(arg1, arg2 ... )
			if($statement instanceof DB_Query_Select_Function){

				$output[] = $this->buildFunctionExpression($statement) . $select_as;
				continue;

			}

			// SELECT any expression required
			if($statement instanceof DB_Query_Select_Expression){

				// expression select
				$output[] = (string)$statement->getExpression() . $select_as;

			}


		}
		return implode(",\n", $output);
	}

	/**
	 * @param DB_Query $query
	 * @return string
	 */
	public function buildFromExpression(DB_Query $query){

		$tables_in_query = $query->getTablesInQuery();
		$output = $this->quoteIdentifier($query->getMainTableName());
		if(count($tables_in_query) == 1){
			return $output;
		}

		$relations = $query->getRelations();
		if($relations->isEmpty()){

			$main_table = $query->getMainTableName();
			foreach($tables_in_query as $table){
				if($table == $main_table){
					continue;
				}
				$output .= ",\n{$this->quoteIdentifier($table)}";
			}
			return $output;

		}



		/** @var $relations DB_Query_Relations_Relation[] */
		foreach($relations as $relation){

			$output .= "\n" . $relation->getJoinType() . " JOIN {$this->quoteIdentifier($relation->getRelatedTableName())} ON (\n\t";
			$output .= str_replace("\n", "\n\t", $this->buildCompareExpression($relation)) . "\n)";

		}
		return rtrim($output);
	}

	/**
	 * @param DB_Query_Compare $compare
	 * @return string
	 */
	public function buildCompareExpression(DB_Query_Compare $compare){
		if($compare->isEmpty()){
			return "";
		}

		$statements = $compare->getStatements();
		$output = array();

		foreach($statements as $statement){
			// operators
			if(is_string($statement)){
				$output[] = $statement;
				continue;
			}

			// nested query
			if($statement instanceof DB_Query_Compare){
				if($statement->isEmpty()){
					continue;
				}
				$output[] = "(";
				$output[] = "\t" . str_replace("\n", "\n\t", $this->buildCompareExpression($statement));
				$output[] = ")";
				continue;
			}

			// compare expression
			$cmp_expr = $this->_buildComparePart($compare, $statement);
			if($cmp_expr !== ""){
				$output[] = $cmp_expr;
			}
		}

		return implode("\n", $output);
	}


	/**
	 * @param DB_Query_Compare $compare_query
	 * @param DB_Query_Compare_Column|DB_Query_Compare_Function|DB_Query_Compare_Expression
	 * @return string
	 */
	protected function _buildComparePart(DB_Query_Compare $compare_query, $statement){

		$query = $compare_query->getQuery();
		$skip_main_table_name = $query->getTablesInQueryCount() == 1;
		$main_table_name = $query->getMainTableName();

		if($statement instanceof DB_Query_Compare_Expression){
			if($statement->getCompareOperator() == null){
				return (string)$statement->getExpression();
			}

			$output = (string)$statement->getExpression() . " ";

		} elseif($statement instanceof DB_Query_Compare_Column){

			$get_table_name = !$skip_main_table_name || $statement->getTableName() != $main_table_name;
			$output = $this->quoteIdentifier($statement->getColumnName($get_table_name)) . " ";

		} elseif($statement instanceof DB_Query_Compare_Function){

			$output = $this->buildFunctionExpression($statement) . " ";

		} else {

			return "";

		}

		$operator = $statement->getCompareOperator();
		$output .= $operator;

		if($statement->isNULLCompare()){
			return $output;
		}


		$value = $statement->getValue();
		if($value instanceof DB_Query){
			return $output . " (\n\t" . str_replace("\n", "\n\t", $this->buildQuery($value)) . "\n)";
		}

		if($value instanceof DB_Table_Column){
			$get_table_name = !$skip_main_table_name || $value->getTableName() != $main_table_name;
			return "{$output} " . $this->quoteIdentifier($value->getColumnName($get_table_name));
		}

		if(!$statement->isINCompare()){
			return "{$output} {$this->quoteValue($value)}";
		}

		return "{$output} (\n\t" .$this->quoteIN($value) . "\n)";
	}

	/**
	 * @param DB_Query_GroupBy $group_by
	 * @return string
	 */
	public function buildGroupByExpression(DB_Query_GroupBy $group_by){

		if($group_by->isEmpty()){
			return "";
		}

		$query = $group_by->getQuery();
		$skip_main_table_name = $query->getTablesInQueryCount() == 1;
		$main_table_name = $query->getMainTableName();

		$output = array();

		/** @var $column DB_Query_Column */
		foreach($group_by as $column){
			$get_table_name = !$skip_main_table_name || $column->getTableName() != $main_table_name;
			$output[] = $this->quoteIdentifier($column->getColumnName($get_table_name));
		}

		return implode(",\n", $output);
	}

	/**
	 * @param DB_Query_OrderBy $order_by
	 * @return string
	 */
	public function buildOrderByExpression(DB_Query_OrderBy $order_by){
		if($order_by->isEmpty()){
			return "";
		}

		$query = $order_by->getQuery();
		$skip_main_table_name = $query->getTablesInQueryCount() == 1;
		$main_table_name = $query->getMainTableName();

		$output = array();

		/** @var $column DB_Query_OrderBy_Column */
		foreach($order_by as $column){
			$get_table_name = !$skip_main_table_name || $column->getTableName() != $main_table_name;
			$output[] = $this->quoteIdentifier($column->getColumnName($get_table_name)) . " " . $column->getOrderHow();
		}

		return implode(",\n", $output);
	}

	/**
	 * @param DB_Query_Function $function
	 * @return string
	 */
	public function buildFunctionExpression(DB_Query_Function $function){

		if(!$function->hasArguments()){
			return "{$function->getFunctionName()}()";
		}

		$query = $function->getQuery();
		$skip_main_table_name = $query->getTablesInQueryCount() == 1;
		$main_table_name = $query->getMainTableName();

		//array|DB_Table_Column[]|DB_Expression[]|DB_Query[]
		$output = array();
		$arguments = $function->getArguments();
		$multi_line = false;
		$length = 0;

		foreach($arguments as $arg){


			if($arg instanceof DB_Table_Column){
				// column
				$get_table_name = !$skip_main_table_name || $arg->getTableName() != $main_table_name;
				$str_value = $this->quoteIdentifier($arg->getColumnName($get_table_name));
			} elseif($arg instanceof DB_Expression){
				// expression
				$str_value = (string)$arg;
			} elseif($arg instanceof DB_Query){
				// query
				$str_value = "(\n\t" . str_replace("\n", "\n\t", $this->buildQuery($arg)) . "\n)";
			} else {
				// other value
				$str_value = $this->quoteValue($arg);
			}

			$multi_line |= (strpos($str_value, "\n") !== false);
			$length += strlen($str_value);
			$output[] = $str_value;

		}

		if($multi_line || $length > 256){
			return "{$function->getFunctionName()}(\n\t".implode(",\n\t", $output)."\n)";
		} else {
			return "{$function->getFunctionName()}(".implode(", ", $output).")";
		}
	}

}
