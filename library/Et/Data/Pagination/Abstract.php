<?php
namespace Et;
abstract class Data_Pagination_Abstract extends Object implements \Iterator,\Countable,\ArrayAccess {

	const ALL_ITEMS = "_all_";
	const DEFAULT_PER_PAGE_LIMIT = 25;

	const DEFAULT_PAGE_NUMBER_PARAMETER = "page_number";
	const DEFAULT_ORDER_BY_PARAMETER = "order_by";
	const DEFAULT_ITEMS_PER_PAGE_PARAMETER = "items_per_page";


	const SORT_ASC = "ASC";
	const SORT_DESC = "DESC";

	/**
	 * @var bool
	 */
	protected $allow_items_serialization = true;

	/**
	 * @var bool
	 */
	protected $allow_sort_multiple = false;

	/**
	 * @var string
	 */
	protected $page_number_parameter = self::DEFAULT_PAGE_NUMBER_PARAMETER;

	/**
	 * @var string
	 */
	protected $order_by_parameter = self::DEFAULT_ORDER_BY_PARAMETER;

	/**
	 * @var string
	 */
	protected $items_per_page_parameter = self::DEFAULT_ITEMS_PER_PAGE_PARAMETER;

	/**
	 * @var int
	 */
	protected $items_count;

	/**
	 * @var int
	 */
	protected $items_per_page = self::DEFAULT_PER_PAGE_LIMIT;

	/**
	 * @var array
	 */
	protected $items_per_page_options = array(
		5 => 5,
		10 => 10,
		25 => 25,
		50 => 50,
		100 => 100
	);

	/**
	 * @var int
	 */
	protected $current_page = 1;

	/**
	 * @var int
	 */
	protected $pages_count;

	/**
	 * @var array[]
	 */
	protected $items = array();

	/**
	 * @var array
	 */
	protected $_current_page_items;

	/**
	 * @var array
	 */
	protected $sort_by = array();


	/**
	 * @return array
	 */
	function __sleep(){
		$values = get_object_vars($this);
		if(!$this->getAllowItemsSerialization()){
			unset($values["items"]);
			unset($values["_current_page_items"]);
		}
		return array_keys($values);
	}

	/**
	 * @param boolean $allow_items_serialization
	 */
	public function setAllowItemsSerialization($allow_items_serialization) {
		$this->allow_items_serialization = (bool)$allow_items_serialization;
	}

	/**
	 * @return boolean
	 */
	public function getAllowItemsSerialization() {
		return $this->allow_items_serialization;
	}

	/**
	 * @param boolean $allow_sort_multiple
	 */
	public function setAllowSortMultiple($allow_sort_multiple) {
		$this->allow_sort_multiple = (bool)$allow_sort_multiple;
	}

	/**
	 * @return boolean
	 */
	public function getAllowSortMultiple() {
		return $this->allow_sort_multiple;
	}


	/**
	 * @param string $order_how
	 * @return string
	 */
	protected function resolveSortHow($order_how){
		return strtoupper($order_how) == static::SORT_DESC
			? static::SORT_DESC
			: static::SORT_ASC;
	}



	/**
	 * @return array
	 */
	public function getSortBy(){
		if(!$this->sort_by || $this->getAllowSortMultiple()){
			return $this->sort_by;
		}
		foreach($this->sort_by as $key => $how){
			return array($key => $how);
		}
		return array();
	}

	/**
	 * @param array $sort_by
	 * @return bool
	 */
	public function sortBy(array $sort_by){
		$this->sort_by = array();
		foreach($sort_by as $key => $how){
			Debug_Assert::isIdentifier($key, "Invalid sort key '{$key}' format");
			if(is_numeric($key)){
				$key = $how;
				$how = static::SORT_ASC;
			}
			$this->sort_by[$key] = $this->resolveSortHow($how);
			if(!$this->getAllowSortMultiple()){
				break;
			}
		}
		return true;
	}



	/**
	 * @param string $key
	 * @param null|string $sort_how [optional]
	 * @return bool
	 */
	public function sortByKey($key, $sort_how = null){
		return $this->sortBy(array($key => $sort_how));
	}


	/**
	 * @param int|array $items_or_count
	 * @param int $items_per_page
	 * @return int
	 */
	public static function calculatePagesCount($items_or_count, $items_per_page){
		if(is_array($items_or_count)){
			$items_or_count = count($items_or_count);
		}

		if((int)$items_or_count <= 0){
			return 0;
		}

		if($items_per_page == static::ALL_ITEMS){
			return 1;
		}

		if((int)$items_per_page <= 0){
			return 0;
		}

		return (int)ceil((int)$items_or_count / (int)$items_per_page);
	}

	/**
	 * @param int $page
	 * @param int $items_per_page
	 * @param null|int|array $items_or_count [optional]
	 * @return int
	 */
	public static function calculateOffsetFrom($page, $items_per_page, $items_or_count = null){
		if((int)$page <= 0 || $items_per_page == static::ALL_ITEMS){
			return 0;
		}

		if((int)$items_per_page <= 0){
			return 0;
		}
		if($items_or_count !== null){
			if((int)$items_or_count <= 0){
				return 0;
			}
			$pages_count = static::calculatePagesCount($items_or_count, $items_per_page);
			if($page > $pages_count){
				return 0;
			}
		}

		return ($page - 1) * $items_per_page;
	}

	/**
	 * @param int $page
	 * @param int $items_per_page
	 * @param null|int|array $items_or_count [optional]
	 * @return int
	 */
	public static function calculateOffsetTo($page, $items_per_page, $items_or_count = null){
		if((int)$page <= 0){
			return 0;
		}

		if($items_per_page == static::ALL_ITEMS){
			if(!$items_or_count){
				return 0;
			}
			if(is_array($items_or_count)){
				return count($items_or_count) - 1;
			} else {
				return max(1, (int)$items_or_count) - 1;
			}
		}

		if((int)$items_per_page <= 0){
			return 0;
		}

		if($items_or_count !== null){
			if((int)$items_or_count <= 0){
				return 0;
			}
			$pages_count = static::calculatePagesCount($items_or_count, $items_per_page);
			if($page > $pages_count){
				return 0;
			}

			return min($items_or_count - 1, max(0, $page * $items_per_page - 1));
		}

		return max(0, $page * $items_per_page - 1);
	}

	/**
	 * @param array $items
	 * @param int $page
	 * @param int $items_per_page
	 * @return array
	 */
	public static function calculatePageKeys(array &$items, $page, $items_per_page){
		if(!$items){
			return array();
		}

		if((int)$page <= 0){
			return array();
		}

		if((int)$items_per_page <= 0){
			return array();
		}

		$items_count = count($items);
		$index_from = static::calculateOffsetFrom($page, $items_per_page, $items_count);
		$index_to = static::calculateOffsetTo($page, $items_per_page, $items_count);

		$keys = array_keys($items);
		$output = array();
		for($i = $index_from; $i <= $index_to; $i++){
			$output[] = $keys[$i];
		}

		return $output;
	}

	public function resetItemsAndCounts(){
		$this->items_count = null;
		$this->pages_count = null;
		$this->current_page = 1;
		$this->items = array();
		$this->_current_page_items = null;
	}

	public function refresh(){

		$this->resetItemsAndCounts();
		$this->items_count = $this->fetchItemsCount();
		$this->pages_count = $this->calculatePagesCount($this->items_count, $this->items_per_page);
		$this->current_page = min($this->current_page, $this->pages_count);

	}


	/**
	 * @return int
	 */
	abstract protected function fetchItemsCount();

	/**
	 * @param int $page
	 * @return array
	 */
	abstract protected function fetchItems($page);


	/**
	 * @param null|int $page [optional]
	 * @param bool $refresh [optional]
	 * @return array
	 */
	public function getItems($page = null, $refresh = false){
		if(!$this->getItemsCount($refresh)){
			return array();
		}

		$current_page = $this->getCurrentPage();
		if($page === null){
			$page = $current_page;
		}

		if($page == $current_page){
			$this->_current_page_items = array();
		}

		if(!$this->hasPage($page) || !$page){
			return array();
		}

		if(!isset($this->items[$page])){
			$this->items[$page] = $this->fetchItems($page);
		}

		if($page == $current_page){
			$this->_current_page_items = &$this->items[$page];
		}

		return $this->items[$page];
	}


	/**
	 * @param bool $refresh [optional]
	 * @return int
	 */
	public function getPagesCount($refresh = false) {
		if($this->pages_count === null || $refresh){
			$this->refresh();
		}
		return $this->pages_count;
	}

	/**
	 * @return int
	 */
	public function getCurrentPage() {
		if(!$this->current_page && $this->getItemsCount() > 0){
			$this->current_page = 1;
		}
		return $this->current_page;
	}

	/**
	 * @param int $page_number
	 * @return bool
	 */
	public function setCurrentPage($page_number){
		$pages_count = $this->getPagesCount();
		if(!$pages_count){
			$this->current_page = 0;
			return $page_number == 0;
		}
		$this->current_page = max(1, min($pages_count, (int)$page_number));
		return $this->current_page == $page_number;
	}

	/**
	 * @return bool
	 */
	public function hasNextPage(){
		return $this->getCurrentPage() < $this->getPagesCount();
	}

	/**
	 * @return bool
	 */
	public function hasPreviousPage(){
		$pages_count = $this->getPagesCount();
		if(!$pages_count){
			return false;
		}
		return $this->getCurrentPage() > 1;
	}

	/**
	 * @return bool
	 */
	public function setNextPage(){
		if($this->hasNextPage()){
			$this->setCurrentPage($this->getCurrentPage() + 1);
			return true;
		}
		return false;
	}

	/**
	 * @return bool
	 */
	public function setPreviousPage(){
		if($this->hasPreviousPage()){
			$this->setCurrentPage($this->getCurrentPage() - 1);
			return true;
		}
		return false;
	}

	/**
	 * @param Data_Array $settings_source [optional] NULL = $_GET
	 * @return bool
	 */
	public function fetchSettings(Data_Array $settings_source = null){
		if(!$settings_source){
			$settings_source = Http_Request::GET();
		}

		$anything_set = false;

		$sort_by_parameter = $this->getOrderByParameter();
		$sort_by = $settings_source->getRaw($sort_by_parameter);
		if(is_array($sort_by)){
			if($this->sortBy($sort_by)){
				$anything_set = true;
			}
		} elseif(is_string($sort_by)){
			$this->sortByKey($sort_by);
		}

		$page_number_parameter = $this->getPageNumberParameter();
		if($settings_source->exists($page_number_parameter)){
			$set = $this->setCurrentPage($settings_source->getInt($page_number_parameter));
			if($set){
				$anything_set = true;
			}
		}

		$items_per_page_parameter = $this->getItemsPerPageParameter();
		if($settings_source->exists($items_per_page_parameter)){
			$set = $this->setItemsPerPage($settings_source->getInt($items_per_page_parameter));
			if($set){
				$anything_set = true;
			}
		}

		return $anything_set;
	}

	/**
	 * @param bool $get_as_string [optional]
	 * @param array|null $order_by [optional]
	 * @return array
	 */
	public function getOrderByQueryForURL($get_as_string = false, array $order_by = null){
		if($order_by === null){
			$order_by = $this->sort_by;
		} else {
			$ob = array();
			foreach($order_by as $k => $v){
				if(is_numeric($k)){
					$k = $v;
					$v = static::SORT_ASC;
				}
				$v = $this->resolveSortHow($v);
				$ob[$k] = $v;
			}
			$order_by = $ob;
		}

		if(!$order_by){
			if($get_as_string){
				return "";
			}
			return array();
		}

		if($get_as_string){
			return http_build_query($order_by);
		}
		return $order_by;
	}

	/**
	 * @param int|null $page_number [optional]
	 * @param null|string $base_URL [optional] NULL = current URL
	 * @param array $order_by [optional]
	 * @return bool|string
	 */
	public function getPageURL($page_number = null, $base_URL = null, array $order_by = null){
		if($page_number === null){
			$page_number = $this->getCurrentPage();
		}

		if(!$this->hasPage($page_number)){
			return false;
		}

		$base_URL = (string)$base_URL;
		if(!$base_URL){
			$base_URL = ET_REQUEST_URL_WITH_QUERY;
		}

		$query_params = array();
		if(strpos($base_URL, "?") !== false){
			list($base_URL, $query_string) = explode("?", $base_URL, 2);
			parse_str($query_string, $query_params);
			if(!is_array($query_params)){
				$query_params = array();
			}
		}

		$page_number_get_key = $this->getPageNumberParameter();
		if(!$page_number){
			if(isset($query_params[$page_number_get_key])){
				unset($query_params[$page_number_get_key]);
			}
		} else {
			$query_params[$page_number_get_key] = $page_number;
		}

		$order_by = $this->getOrderByQueryForURL(false, $order_by);
		$order_by_parameter = $this->getOrderByParameter();
		if(!$order_by){
			if(isset($query_params[$order_by_parameter])){
				unset($query_params[$order_by_parameter]);
			}
		} else {
			$query_params[$order_by_parameter] = $order_by;
		}

		$count_param = $this->getItemsPerPageParameter();
		if(count($this->items_per_page_options) > 1){
			$query_params[$count_param] = $this->getItemsPerPage();
		} else {
			if(isset($query_params[$count_param])){
				unset($query_params[$count_param]);
			}
		}

		return $query_params
			? $base_URL . "?" . http_build_query($query_params)
			: $base_URL;
	}


	/**
	 * @param null|string $base_URL [optional] NULL = current URL
	 * @return bool|string
	 */
	public function getNextPageURL($base_URL = null){
		if(!$this->hasNextPage()){
			return false;
		}
		return $this->getPageURL($this->getNextPageNumber(), $base_URL);
	}

	/**
	 * @param null|string $base_URL [optional] NULL = current URL
	 * @return bool|string
	 */
	public function getPreviousPageURL($base_URL = null){
		if(!$this->hasPreviousPage()){
			return false;
		}
		return $this->getPageURL($this->getPreviousPageNumber(), $base_URL);
	}

	/**
	 * @param null|string $base_URL [optional] NULL = current URL
	 * @return bool|string
	 */
	public function getFirstPageURL($base_URL = null){
		if(!$this->hasPage(1)){
			return false;
		}
		return $this->getPageURL(1, $base_URL);
	}

	/**
	 * @param null|string $base_URL [optional] NULL = current URL
	 * @return bool|string
	 */
	public function getLastPageURL($base_URL = null){
		$pages_count = $this->getPagesCount();
		if(!$pages_count){
			return false;
		}
		return $this->getPageURL($pages_count, $base_URL);
	}

	/**
	 * @param null|string $base_URL [optional] NULL = current URL
	 * @return bool|string
	 */
	public function getCurrentPageURL($base_URL = null){
		return $this->getPageURL($this->getCurrentPage(), $base_URL);
	}

	/**
	 * @return int
	 */
	public function getItemsPerPage() {
		return $this->items_per_page;
	}

	/**
	 * @param array $items_per_page_options
	 * @return bool
	 */
	public function setItemsPerPageOptions(array $items_per_page_options) {
		if(!$items_per_page_options){
			return false;
		}

		$is_linear_array = true;
		$counter = 0;
		foreach($items_per_page_options as $k => $v){
			if($k != $counter){
				$is_linear_array = false;
				break;
			}
			$counter++;
		}

		foreach($items_per_page_options as $k => $v){
			$value = $is_linear_array ? $v : $k;
			if((int)$value <= 0){
				unset($items_per_page_options[$k]);
			}
		}

		if(!$items_per_page_options){
			return false;
		}

		$this->items_per_page_options = array();

		foreach($items_per_page_options as $k => $v){
			if($is_linear_array){
				$limit = (int)$v;
				$label = (string)$v;
			} else {
				$limit = (int)$k;
				$label = (string)$v;
			}
			$this->items_per_page_options[$limit] = $label;
		}
		ksort($this->items_per_page_options);

		$items_per_page = $this->getItemsPerPage();
		if(isset($this->items_per_page_options[$items_per_page])){
			return true;
		}

		$new_items_per_page = null;
		foreach($this->items_per_page_options as $value => $label){
			if($new_items_per_page === null || $value <= $new_items_per_page){
				$new_items_per_page = $value;
			} else {
				break;
			}
		}

		return $this->setItemsPerPage($new_items_per_page);
	}

	/**
	 * @return array
	 */
	public function getItemsPerPageOptions() {
		return $this->items_per_page_options;
	}



	/**
	 * @return bool|int
	 */
	public function getNextPageNumber(){
		if(!$this->hasNextPage()){
			return false;
		}
		return $this->getCurrentPage() + 1;
	}

	/**
	 * @return bool|int
	 */
	public function getPreviousPageNumber(){
		if(!$this->hasPreviousPage()){
			return false;
		}
		return $this->getCurrentPage() - 1;
	}

	/**
	 * @param int $items_per_page
	 * @return bool
	 */
	public function setItemsPerPage($items_per_page) {
		if(!isset($this->items_per_page_options[$items_per_page])){
			return false;
		}

		$items_per_page = max(1, (int)$items_per_page);
		$same_count = $items_per_page == $this->items_per_page;
		$this->items_per_page = $items_per_page;
		if(!$same_count && $this->items_count !== null){
			$this->refresh();
		}

		return true;
	}


	/**
	 * @param bool $refresh [optional]
	 * @return int
	 */
	public function getItemsCount($refresh = false) {
		if($this->items_count === null || $refresh){
			$this->refresh();
		}
		return $this->items_count;
	}

	/**
	 * @param bool $refresh [optional]
	 * @return bool
	 */
	public function isEmpty($refresh = false){
		return !$this->getPagesCount($refresh);
	}

	/**
	 * @param int $page_number
	 * @param bool $refresh [optional[
	 * @return bool
	 */
	public function hasPage($page_number, $refresh = false){
		if(!is_numeric($page_number)){
			return false;
		}
		$pages_count = $this->getPagesCount($refresh);
		if(!$pages_count){
			return $page_number == 0;
		}
		return $page_number > 0 && $page_number <= $pages_count;
	}

	/**
	 * @param string $items_count_parameter
	 */
	public function setItemsPerPageParameter($items_count_parameter) {
		Debug_Assert::isIdentifier($items_count_parameter);
		$this->items_per_page_parameter = $items_count_parameter;
	}

	/**
	 * @return string
	 */
	public function getItemsPerPageParameter() {
		return $this->items_per_page_parameter;
	}

	/**
	 * @param string $order_by_parameter
	 */
	public function setOrderByParameter($order_by_parameter) {
		Debug_Assert::isIdentifier($order_by_parameter);
		$this->order_by_parameter = $order_by_parameter;
	}

	/**
	 * @return string
	 */
	public function getOrderByParameter() {
		return $this->order_by_parameter;
	}

	/**
	 * @param string $page_number_parameter
	 */
	public function setPageNumberParameter($page_number_parameter) {
		Debug_Assert::isIdentifier($page_number_parameter);
		$this->page_number_parameter = $page_number_parameter;
	}

	/**
	 * @return string
	 */
	public function getPageNumberParameter() {
		return $this->page_number_parameter;
	}



	/**
	 * @return bool|mixed|array
	 */
	public function current() {
		if($this->_current_page_items === null){
			$this->getItems();
		}
		return current($this->_current_page_items);
	}


	public function next() {
		if($this->_current_page_items === null){
			$this->getItems();
		}
		next($this->_current_page_items);
	}

	/**
	 * @return int|string|null
	 */
	public function key() {
		if($this->_current_page_items === null){
			$this->getItems();
		}
		return key($this->_current_page_items);
	}

	/**
	 * @return bool
	 */
	public function valid() {
		if($this->_current_page_items === null){
			$this->getItems();
		}
		return key($this->_current_page_items) !== null;

	}

	public function rewind() {
		if($this->_current_page_items === null){
			$this->getItems();
		}
		reset($this->_current_page_items);
	}

	/**
	 * @param int $page_number
	 * @return bool
	 */
	public function offsetExists($page_number) {
		return $this->hasPage($page_number);
	}

	/**
	 * @param int $page_number
	 * @return array|array[]
	 */
	public function offsetGet($page_number) {
		return $this->getItems($page_number);
	}

	/**
	 * @param mixed $offset
	 * @param mixed $value
	 * @throws DB_Query_Exception
	 */
	public function offsetSet($offset, $value) {
		Debug::triggerError("Cannot use " . static::class . "::offsetSet()");
	}

	/**
	 * @param mixed $offset
	 * @throws DB_Query_Exception
	 */
	public function offsetUnset($offset) {
		Debug::triggerError("Cannot use " . static::class . "::offsetUnset()");
	}

	/**
	 * Get pages count
	 *
	 * @return int
	 */
	public function count() {
		return $this->getPagesCount();
	}
}