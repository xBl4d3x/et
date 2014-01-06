<?php
namespace Et;
class Session_Namespace extends Data_Array {

	/**
	 * @var string
	 */
	protected $namespace_name;

	/**
	 * @param string|null $namespace_name
	 */
	function __construct($namespace_name){

		if(!Session::getSessionStarted()){
			Session::startSession();
		}


		if(!$namespace_name){
			$namespace_name = Session::DEFAULT_NAMESPACE;
		}

		Debug_Assert::isIdentifier($namespace_name);
		$this->namespace_name = $namespace_name;
		$session_key = $this->getSessionKey();

		if(!isset($_SESSION)){
			$_SESSION = array();
		}

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
		return Session::getNamespace($namespace_name);
	}


	/**
	 * @param Session_Namespace $namespace
	 * @param null|string $import_at_path [optional]
	 */
	public function importNamespace(Session_Namespace $namespace, $import_at_path = null){
		$data = $namespace->getData();
		if($import_at_path === null){
			foreach($data as $k => $v){
				$this->data[$k] = $v;
			}
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