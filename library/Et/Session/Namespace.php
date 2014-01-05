<?php
namespace Et;
class Session_Namespace extends Data_Array {

	const FLAGS_KEY = "___flags___";

	/**
	 * @var string
	 */
	protected $namespace_name;

	/**
	 * @param string|null $namespace_name
	 */
	function __construct($namespace_name){

		if(!Session::isInitialized()){
			Session::initialize();
		}

		Debug_Assert::isIdentifier($namespace_name);
		$this->namespace_name = $namespace_name;
		$session_key = $this->getSessionKey();

		if(!isset($_SESSION[$session_key]) || !is_array($_SESSION[$session_key])){
			$_SESSION[$session_key] = array();
		}

		$this->data = &$_SESSION[$session_key];
	}

	/**
	 * @return string
	 */
	public function getNamespaceName() {
		return $this->namespace_name;
	}

	/**
	 * @return string
	 */
	public function getSessionKey(){
		return Session::getNamespaceSessionKey($this->getNamespaceName());
	}

	/**
	 * @param null|string $namespace_name [optional]
	 * @return Session_Namespace
	 */
	public static function getInstance($namespace_name = null){
		return Session::get($namespace_name);
	}

	/**
	 * @param string $flag_name
	 * @param int|mixed $flag_value [optional]
	 */
	public function setFlag($flag_name, $flag_value = 1){
		if(!isset($this->data[static::FLAGS_KEY])){
			$this->data[static::FLAGS_KEY] = array();
		}
		$this->data[static::FLAGS_KEY][$flag_name] = $flag_value;
	}

	/**
	 * @param string $flag_name
	 * @return bool
	 */
	public function testFlag($flag_name){
		return !empty($this->data[static::FLAGS_KEY][$flag_name]);
	}

	/**
	 * @param string $flag_name
	 * @return bool
	 */
	public function resetFlag($flag_name){
		if(isset($this->data[static::FLAGS_KEY][$flag_name])){
			unset($this->data[static::FLAGS_KEY][$flag_name]);
			return true;
		}
		return false;
	}

	/**
	 * @param string $flag_name
	 * @param null|mixed $flag_value [optional[reference] If flag is found, it's value is set here
	 * @return bool
	 */
	function testAndResetFlag($flag_name, &$flag_value = null){
		if(!$this->testFlag($flag_name)){
			return false;
		}
		$flag_value = $this->data[static::FLAGS_KEY][$flag_name];
		$this->resetFlag($flag_name);
		return true;
	}


	/**
	 * @param Session_Namespace $namespace
	 * @param null|string $import_at_path [optional]
	 */
	public function importNamespace(Session_Namespace $namespace, $import_at_path = null){
		$data = $namespace->getData();
		if($import_at_path === null){
			$this->data = array_merge($this->data, $data);
			return;
		}

		$ref = $this->getReference($import_at_path, $found);
		if($found && is_array($ref)){
			foreach($data as $k => $v){
				$ref[$k] = $v;
			}
		} else {
			$this->setValue($import_at_path, $data);
		}
	}
}