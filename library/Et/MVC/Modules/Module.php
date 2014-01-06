<?php
namespace Et;
abstract class MVC_Modules_Module extends Object {

	/**
	 * @var array
	 */
	protected $_signal_handlers_identifiers = array();

	/**
	 * @var string
	 */
	protected $module_ID;

	/**
	 * @var MVC_Modules_Module_Metadata
	 */
	protected $module_metadata;

	/**
	 * @var bool
	 */
	protected $initialized = false;

	/**
	 * @var array
	 */
	protected static $auth_operations_list = array();


	/**
	 * @param MVC_Modules_Module_Metadata $module_metadata
	 * @param bool $initialize [optional]
	 */
	function __construct(MVC_Modules_Module_Metadata $module_metadata, $initialize = true){
		$this->module_metadata = $module_metadata;
		$this->module_ID = $module_metadata->getModuleID();

		if($initialize){
			$this->initialize();
		}
	}

	protected function initialize(){
		$this->initializeFactoryOverloads();
		$this->initializeSignalHandlers();
		$this->initialized = true;
	}


	/**
	 * @return bool
	 */
	function isInitialized(){
		return $this->initialized;
	}

	protected function initializeSignalHandlers(){
		$signal_handlers = $this->module_metadata->getSignalHandlers();
		if(!$signal_handlers){
			return;
		}

		foreach($signal_handlers as $signal_name => $handler){
			if(!method_exists($this, $handler)){
				throw new MVC_Modules_Exception(
					"Method " . static::class . "::{$handler}() does not exist, may not be used as signal handler",
					MVC_Modules_Exception::CODE_INVALID_METADATA
				);
			}
			$this->_signal_handlers_identifiers[$signal_name] = System::subscribeSignal($signal_name, array($this, $handler));
		}
	}

	protected function initializeFactoryOverloads(){
		$overloads = $this->module_metadata->getFactoryClassMap();
		if($overloads){
			Factory::setClassOverrideMap($overloads);
		}
	}

	/**
	 * @param null|string|array $rest_of_path
	 * @param null|string|array $query_parameters
	 * @return string
	 */
	protected function buildRestOfURL($rest_of_path = null, $query_parameters = null){
		$URI = "";
		if($rest_of_path !== null){
			if(is_array($rest_of_path)){
				$rest_of_path = implode("/", $rest_of_path);
			}
			$rest_of_path = trim("/", $rest_of_path) . "/";
			if($rest_of_path != "/"){
				$URI .= $rest_of_path;
			}
		}

		if($query_parameters !== null){
			if(is_array($query_parameters)){
				$query_parameters = http_build_query($query_parameters);
			}
			$query_parameters = trim($query_parameters);
			if($query_parameters !== ""){
				$URI .= "?{$query_parameters}";
			}
		}
		return $URI;
	}

	/**
	 * @param null|string|array $rest_of_path
	 * @param null|string|array $query_parameters
	 * @return string
	 */
	public function getModuleURI($rest_of_path = null, $query_parameters = null) {
		$URI = $this->module_metadata->getModuleURI();
		return $URI . $this->buildRestOfURL($rest_of_path, $query_parameters);
	}

	/**
	 * @param null|string|array $rest_of_path
	 * @param null|string|array $query_parameters
	 * @return string
	 */
	public function getModuleURL($rest_of_path = null, $query_parameters = null) {
		$URL = $this->module_metadata->getModuleURL();
		return $URL . $this->buildRestOfURL($rest_of_path, $query_parameters);
	}

	/**
	 * @param null|string|array $rest_of_path
	 * @param null|string|array $query_parameters
	 * @return string
	 */
	public function getModuleStaticURI($rest_of_path = null, $query_parameters = null){
		$URI = $this->module_metadata->getModuleURI() . "static/";
		return $URI . $this->buildRestOfURL($rest_of_path, $query_parameters);
	}

	/**
	 * @param null|string|array $rest_of_path
	 * @param null|string|array $query_parameters
	 * @return string
	 */
	public function getModuleStaticURL($rest_of_path = null, $query_parameters = null){
		$URI = $this->module_metadata->getModuleURL() . "static/";
		return $URI . $this->buildRestOfURL($rest_of_path, $query_parameters);
	}

	/**
	 * @param null|string $script_name
	 * @param null|string|array $query_parameters
	 * @return string
	 */
	public function getModuleScriptsURI($script_name = null, $query_parameters = null){
		$URI = $this->module_metadata->getModuleURI() . "scripts/";
		$rest_of_path = null;
		if($script_name){
			Debug_Assert::isStringMatching($script_name, '^[\w-]+(?:/[\w-]+)*$', 'Invalid script name format');
			$rest_of_path = $script_name . ".php";
		}
		return $URI . $this->buildRestOfURL($rest_of_path, $query_parameters);
	}

	/**
	 * @param null|string $script_name
	 * @param null|string|array $query_parameters
	 * @return string
	 */
	public function getModuleScriptsURL($script_name = null, $query_parameters = null){
		$URI = $this->module_metadata->getModuleURL() . "scripts/";
		$rest_of_path = null;
		if($script_name){
			Debug_Assert::isStringMatching($script_name, '^[\w-]+(?:/[\w-]+)*$', 'Invalid script name format');
			$rest_of_path = $script_name . ".php";
		}
		return $URI . $this->buildRestOfURL($rest_of_path, $query_parameters);
	}

	/**
	 * @return System_Dir
	 */
	public function getModuleDirectory() {
		return $this->module_metadata->getModuleDirectory();
	}

	/**
	 * @return MVC_Modules_Module_Metadata
	 */
	public function getModuleMetadata() {
		return $this->module_metadata;
	}

	/**
	 * @return string
	 */
	public function getModuleID() {
		return $this->module_ID;
	}

	/**
	 * @return string
	 */
	public function getModuleTitle(){
		return $this->module_metadata->getModuleTitle();
	}

	/**
	 * @param string $view_name
	 * @param array $view_data [optional]
	 * @return string
	 */
	public function getRenderedView($view_name, array $view_data = array()){
		$view = new MVC_View($this);
		$view->setData($view_data);
		return $view->renderView($view_name);
	}

	/**
	 * @return array
	 */
	public static function getAuthOperationsList() {
		return static::$auth_operations_list;
	}


	/**
	 * @param string $operation_name
	 * @return bool
	 * @throws MVC_Modules_Exception
	 */
	public function checkAuthOperation($operation_name){
		if(!isset(static::$auth_operations_list[$operation_name])){
			throw new MVC_Modules_Exception(
				"Module {$this->getModuleID()} does not have auth action '{$operation_name}' defined - check " . static::class . "::\$auth_actions_list",
				MVC_Modules_Exception::CODE_INVALID_AUTH_ACTION
			);
		}
		return Auth::checkModuleAction($this->getModuleID(), $operation_name);
	}

	/**
	 * @return string
	 */
	function __toString(){
		return $this->getModuleID();
	}

}