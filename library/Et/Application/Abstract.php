<?php
namespace Et;
abstract class Application_Abstract extends Object {

	/**
	 * @var array
	 */
	protected $_signal_handlers_identifiers = array();

	/**
	 * @var string
	 */
	protected $ID;

	/**
	 * @var Application_Metadata
	 */
	protected $metadata;

	/**
	 * @var bool
	 */
	protected $initialized = false;

	/**
	 * @var Locales_Locale
	 */
	protected $current_locale;

	/**
	 * @var string
	 */
	protected $current_base_URL;

	/**
	 * @var bool
	 */
	protected $is_SSL_request = false;

	/**
	 * @var array
	 */
	protected $base_URLs = array();

	/**
	 * @var array
	 */
	protected $base_SSL_URLs = array();


	/**
	 * @param Application_Metadata $application_metadata
	 * @param bool $initialize [optional]
	 */
	function __construct(Application_Metadata $application_metadata, $initialize = true){
		$this->metadata = $application_metadata;
		$this->ID = $application_metadata->getApplicationID();

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
		$signal_handlers = $this->metadata->getSignalHandlers();
		if(!$signal_handlers){
			return;
		}

		foreach($signal_handlers as $signal_name => $handler){
			if(!method_exists($this, $handler)){
				throw new Application_Exception(
					"Method " . static::class . "::{$handler}() does not exist, may not be used as signal handler",
					Application_Exception::CODE_INVALID_METADATA
				);
			}
			$this->_signal_handlers_identifiers[$signal_name] = System::subscribeSignal($signal_name, array($this, $handler));
		}
	}

	protected function initializeFactoryOverloads(){
		$overloads = $this->metadata->getFactoryClassMap();
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
	public function getApplicationStaticURI($rest_of_path = null, $query_parameters = null){
		$URI = $this->metadata->getApplicationURI() . "static/";
		return $URI . $this->buildRestOfURL($rest_of_path, $query_parameters);
	}

	/**
	 * @param null|string|array $rest_of_path
	 * @param null|string|array $query_parameters
	 * @return string
	 */
	public function getApplicationStaticURL($rest_of_path = null, $query_parameters = null){
		$URI = $this->metadata->getApplicationURL() . "static/";
		return $URI . $this->buildRestOfURL($rest_of_path, $query_parameters);
	}

	/**
	 * @param null|string $script_name
	 * @param null|string|array $query_parameters
	 * @return string
	 */
	public function getApplicationScriptsURI($script_name = null, $query_parameters = null){
		$URI = $this->metadata->getApplicationURI() . "scripts/";
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
	public function getApplicationScriptsURL($script_name = null, $query_parameters = null){
		$URI = $this->metadata->getApplicationURL() . "scripts/";
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
	public function getApplicationDirectory() {
		return $this->metadata->getApplicationDirectory();
	}

	/**
	 * @return Application_Metadata
	 */
	public function getMetadata() {
		return $this->metadata;
	}

	/**
	 * @return string
	 */
	public function getID() {
		return $this->ID;
	}

	/**
	 * @return string
	 */
	public function getApplicationName(){
		return $this->metadata->getApplicationName();
	}


	/**
	 * @return string
	 */
	function __toString(){
		return $this->getID();
	}

}