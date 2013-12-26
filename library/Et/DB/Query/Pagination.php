<?php
namespace Et;
class DB_Query_Pagination extends Data_Pagination_Abstract {

	
	const FETCH_COLUMN = "fetch_column";
	const FETCH_PAIRS = "fetch_pairs";
	const FETCH_ROWS = "fetch_rows";
	const FETCH_ROWS_ASSOCIATIVE = "fetch_rows_associative";

	const SORT_ASC = DB_Query::ORDER_ASC;
	const SORT_DESC = DB_Query::ORDER_DESC;


	/**
	 * @var array
	 */
	protected static $_allowed_fetch_types = array(
		self::FETCH_COLUMN,
		self::FETCH_PAIRS,
		self::FETCH_ROWS,
		self::FETCH_ROWS_ASSOCIATIVE,	
	);

	/**
	 * @var string
	 */
	protected $fetch_type = self::FETCH_ROWS;

	/**
	 * @var DB_Query
	 */
	protected $query;

	/**
	 * @var DB_Adapter_Abstract
	 */
	protected $db;

	/**
	 * @param string $name
	 * @param DB_Query $query
	 * @param string|null $fetch_type [optional]
	 * @param DB_Adapter_Abstract $db [optional]
	 */
	function __construct($name, DB_Query $query, $fetch_type = null, DB_Adapter_Abstract $db = null){
		$this->setQuery($query);
		$this->db = $db;
		if(!$fetch_type){
			$fetch_type = static::getDefaultFetchType();
		}
		$this->setFetchType($fetch_type);
		parent::__construct($name);
	}

	/**
	 * @param DB_Query $query
	 */
	public function setQuery(DB_Query $query){
		$query->limit(null, null);
		$this->query = $query;
		$this->resetItemsAndCounts();
	}

	/**
	 * @return string
	 */
	public static function getDefaultFetchType(){
		return static::FETCH_ROWS;
	}


	/**
	 * @param string $fetch_type
	 * @throws DB_Query_Exception
	 */
	protected function checkFetchType($fetch_type){
		if(!in_array($fetch_type, static::$_allowed_fetch_types)){
			throw new DB_Query_Exception(
				"Invalid fetch type '{$fetch_type}' used - allowed types: '" . implode("', '", static::$_allowed_fetch_types) . "'",
				DB_Query_Exception::CODE_INVALID_FETCH_TYPE
			);
		}
	}

	/**
	 * @param string $fetch_type
	 */
	public function setFetchType($fetch_type) {
		$this->checkFetchType($fetch_type);
		if($fetch_type != $this->fetch_type){
			$this->items = array();
		}
		$this->fetch_type = $fetch_type;
	}

	/**
	 * @return string
	 */
	public function getFetchType() {
		return $this->fetch_type;
	}
	

	/**
	 * @return \Et\DB_Query
	 */
	public function getQuery() {
		return $this->query;
	}

	/**
	 * @return DB_Adapter_Abstract
	 */
	protected function getDB(){
		if($this->db){
			return $this->db;
		}
		$db = $this->query->getDB();
		if($db){
			return $db;
		}
		return DB::get();
	}

	/**
	 * @return int
	 */
	protected function fetchItemsCount() {
		$this->query->limit(null, null);
		return $this->getDB()->fetchRowsCount($this->query, array(), true);
	}

	/**
	 * @param int $page
	 * @return array
	 */
	protected function fetchItems($page){
		$query = $this->prepareFetchQuery($page);
		$db = $this->getDB();
		$items = $this->fetchByType($query, $db, $this->getFetchType());
		return $items;
	}

	/**
	 * @param DB_Query $query
	 * @param DB_Adapter_Abstract $db
	 * @param string $fetch_type
	 * @return array
	 */
	protected function fetchByType(DB_Query $query, DB_Adapter_Abstract $db,  $fetch_type){

		switch($fetch_type){
			case self::FETCH_COLUMN:
				return $db->fetchColumn($query);
				break;
			case self::FETCH_PAIRS:
				return $db->fetchPairs($query);
				break;
			case self::FETCH_ROWS_ASSOCIATIVE:
				return $db->fetchRowsAssociative($query);
				break;
		}

		return $this->getDB()->fetchRows($query);
	}

	/**
	 * @param int $page
	 * @return DB_Query
	 */
	protected function prepareFetchQuery($page){
		$offset = $this->calculateOffsetFrom($page, $this->getItemsPerPage(), $this->getItemsCount());
		$order_by = $this->getSortBy(true);

		$fetch_query = $this->getQuery()->cloneInstance()->setDB($this->getDB());
		$fetch_query->getOrderBy()->addOrderByColumns($order_by);
		$fetch_query->limit($this->getItemsPerPage(), $offset);

		return $fetch_query;
	}
}