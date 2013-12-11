<?php
namespace Et;
class Debug_Error implements \JsonSerializable {

	/**
	 * @var string
	 */
	protected $error_ID;

	/**
	 * @var int
	 */
	protected $timestamp;

	/**
	 * @var Exception|Exception_PHPError|\Exception
	 */
	protected $error;

	/**
	 * @var bool
	 */
	protected $logged = false;

	/**
	 * @var bool
	 */
	protected $displayed = false;

	/**
	 * @var bool
	 */
	protected $occurred_on_shutdown = false;

	/**
	 * @var bool
	 */
	protected $strict_mode_enabled = false;

	/**
	 * @var string
	 */
	protected $URL;

	/**
	 * @param \Exception $error
	 */
	function __construct(\Exception $error){
		$this->error = $error;
		$this->timestamp = @time();
		if(PHP_SAPI != "cli" && defined("ET_REQUEST_URL_WITH_QUERY")){
			$this->URL = ET_REQUEST_URL_WITH_QUERY;
		}
	}

	/**
	 * @param string $URL
	 */
	public function setURL($URL) {
		$this->URL = trim($URL);
	}

	/**
	 * @return string
	 */
	public function getURL() {
		return $this->URL;
	}

	/**
	 * @return Debug_Error|null
	 */
	function getPreviousError(){
		/** @var $previous \Exception */
		$previous = $this->error->getPrevious();
		if(!$previous){
			return null;
		}
		return new Debug_Error($previous);
	}

	/**
	 * @param boolean $displayed
	 */
	public function setDisplayed($displayed) {
		$this->displayed = (bool)$displayed;
	}

	/**
	 * @param boolean $logged
	 */
	public function setLogged($logged) {
		$this->logged = (bool)$logged;
	}

	/**
	 * @param boolean $on_shutdown
	 */
	public function setOccurredOnShutdown($on_shutdown) {
		$this->occurred_on_shutdown = (bool)$on_shutdown;
	}

	/**
	 * @param int $from_index [optional]
	 * @param null|int $max_steps [optional]
	 *
	 * @return array
	 */
	public function getBacktrace($from_index = 0, $max_steps = null) {
		$backtrace = $this->error instanceof Exception
					? $this->error->getDebugBacktrace()
					: $this->error->getTrace();

		et_require('Debug_Error_Handler');
		return Debug_Error_Handler::normalizeBacktrace($backtrace, $from_index, $max_steps);
	}

	/**
	 * @return array|mixed
	 */
	public function getContextData() {
		if(!$this->hasContextData()){
			return null;
		}
		return $this->error->getContextData();
	}

	/**
	 * @return bool
	 */
	public function hasContextData(){
		et_require('Exception');
		return $this->error instanceof Exception && $this->error->getContextData();
	}

	/**
	 * @return boolean
	 */
	public function isDisplayed() {
		return $this->displayed;
	}

	/**
	 * @return int
	 */
	public function getErrorCode() {
		return $this->error->getCode();
	}

	/**
	 * @return string
	 */
	public function getErrorCodeLabel() {
		et_require('Exception');
		return $this->error instanceof Exception
				? $this->error->getErrorCodeLabel()
				: "";
	}

	/**
	 * @return string
	 */
	public function getErrorMessage() {
		return $this->error->getMessage();
	}

	/**
	 * @return string
	 */
	public function getExceptionClass() {
		return get_class($this->error);
	}

	/**
	 * @return string
	 */
	public function getFile() {
		return $this->error->getFile();
	}

	/**
	 * @return boolean
	 */
	public function isFatal() {
		return $this->isException() || $this->error->isFatal();
	}

	/**
	 * @return int
	 */
	public function getLine() {
		return $this->error->getLine();
	}

	/**
	 * @return boolean
	 */
	public function isLogged() {
		return $this->logged;
	}

	/**
	 * @return boolean
	 */
	public function hasOccurredOnShutdown() {
		return $this->occurred_on_shutdown;
	}

	/**
	 * @return int
	 */
	public function getTimestamp() {
		return $this->timestamp;
	}

	/**
	 * @param boolean $strict_mode_enabled
	 * @return Debug_Error
	 */
	public function setStrictModeEnabled($strict_mode_enabled) {
		$this->strict_mode_enabled = (bool)$strict_mode_enabled;
		return $this;
	}

	/**
	 * @return boolean
	 */
	public function getStrictModeEnabled() {
		return $this->strict_mode_enabled;
	}

	/**
	 * @return bool
	 */
	function isException(){
		return !$this->isError();
	}

	/**
	 * @return bool
	 */
	function isError(){
		et_require('Exception_PHPError');
		return $this->error instanceof Exception_PHPError;
	}

	/**
	 * @return array
	 */
	function jsonSerialize() {
		$props = get_object_vars($this);
		unset($props["error"]);
		$e = $this->error;
		$previous = $this->getPreviousError();
		$props["error_ID"] = $this->getErrorID();


		$error = array(
			"file" => $e->getFile(),
			"line" => $e->getLine(),
			"code" => $e->getCode(),
			"code_label" => $this->getErrorCodeLabel(),
			"is_fatal" => $this->isFatal(),
			"exception_class" => $this->getExceptionClass(),
			"message" => $e->getMessage(),
			"debug_backtrace" => $this->getBacktrace(),
			"previous" => $previous ? $previous->jsonSerialize() : null
		);

		$props += $error;


		return $props;
	}

	/**
	 * @return string
	 */
	function getErrorID(){
		if($this->error_ID === null){
			$this->error_ID = md5("{$this->getFile()}:{$this->getLine()}:{$this->getErrorCode()}:{$this->getErrorMessage()}");
		}
		return $this->error_ID;
	}

}