<?php
namespace Et;
et_require("Object");
class Data_Validation_Result extends Object implements \JsonSerializable,\Iterator,\ArrayAccess,\Countable {

	/**
	 * @var \Et\Data_Validation_Result_Error[]
	 */
	protected $errors = array();

	/**
	 * @return \Et\Data_Validation_Result_Error[]
	 */
	public function getErrors() {
		return $this->errors;
	}

	/**
	 * @return array
	 */
	public function getErrorNames(){
		return array_keys($this->errors);
	}

	/**
	 * @return array
	 */
	public function getErrorCodes(){
		$output = array();
		foreach($this->errors as $name => $error){
			$output[$name] = $error->getErrorCode();
		}
		return $output;
	}

	/**
	 * @return array
	 */
	public function getErrorMessages(){
		$output = array();
		foreach($this->errors as $name => $error){
			$output[$name] = $error->getErrorMessage();
		}
		return $output;
	}

	public function resetErrors(){
		$this->errors = array();
	}

	/**
	 * @param string|\Et\Data_Validation_Result_Error $error_instance_or_name
	 * @param string|null $error_code [optional] If $error_instance_or_name is not instance of error, error code is mandatory
	 * @param string $error_message [optional]
	 * @param array $error_message_data [optional]
	 */
	public function setError($error_instance_or_name, $error_code = null, $error_message = "", array $error_message_data = array()){
		if(!$error_instance_or_name instanceof Data_Validation_Result_Error){
			$error_instance_or_name = $this->createErrorInstance($error_instance_or_name, $error_code, $error_message, $error_message_data);	
		}	
		$this->errors[$error_instance_or_name->getErrorName()] = $error_instance_or_name;
	}

	/**
	 * @param string $error_name
	 * @return bool|\Et\Data_Validation_Result_Error
	 */
	public function getError($error_name){
		$error_name = (string)$error_name;
		return isset($this->errors[$error_name]) 
				? $this->errors[$error_name]
				: false;
	}

	/**
	 * @param string $error_name
	 * @return bool
	 */
	public function removeError($error_name){
		if(isset($this->errors[$error_name])){
			unset($this->errors[$error_name]);
			return true;
		}
		return false;
	}

	/**
	 * @param string $error_name
	 * @return bool
	 */
	public function getErrorExists($error_name){
		return isset($this->errors[$error_name]);	
	}

	/**
	 * @param string $error_name
	 * @param string $error_code
	 * @param string $error_message
	 * @param array $error_message_data
	 * @return Data_Validation_Result_Error
	 */
	protected function createErrorInstance($error_name, $error_code, $error_message, array $error_message_data){
		Debug_Assert::isVariableName($error_code, "Invalid error code format");
		if($error_message_data){
			$error_message = System::getText($error_message)->replaceData($error_message_data);
		}
		return new Data_Validation_Result_Error($error_name, $error_code, $error_message);
	}

	/**
	 * @return bool
	 */
	public function isValid(){
		return !$this->errors;
	}

	/**
	 * @return int
	 */
	function getErrorsCount(){
		return count($this->errors);
	}

	/**
	 * @return array[]
	 */
	public function jsonSerialize() {
		return $this->errors;
	}

	/**
	 * @return \Et\Data_Validation_Result_Error|bool
	 */
	public function current() {
		return current($this->errors);
	}

	public function next() {
		next($this->errors);
	}

	/**
	 * @return string|null
	 */
	public function key() {
		return key($this->errors);
	}

	/**
	 * @return bool
	 */
	public function valid() {
		return key($this->errors) !== null;
	}

	
	public function rewind() {
		reset($this->errors);
	}

	/**
	 * @param string $error_name
	 * @return bool
	 */
	public function offsetExists($error_name) {
		return $this->getErrorExists($error_name);
	}

	/**
	 * @param string $error_name
	 * @return bool|Data_Validation_Result_Error
	 */
	public function offsetGet($error_name) {
		return $this->getError($error_name);
	}

	/**
	 * @param string|null $error_name
	 * @param \Et\Data_Validation_Result_Error|string|array $value
	 */
	public function offsetSet($error_name, $value) {
		if($value instanceof Data_Validation_Result_Error){
			$this->setError($value);
			return;
		}

		if(is_scalar($value)){
			$this->setError($error_name, $value);
		}

		if(is_array($value)){
			$error_code = array_shift($value);
			$error_message = "";
			$error_data = array();

			if($value){
				$error_message = array_shift($value);
			}

			if($value){
				$error_data = array_shift($value);
			}

			$this->setError($error_name, $error_code, $error_message, $error_data);
		}
	}

	/**
	 * @param string $error_name
	 */
	public function offsetUnset($error_name) {
		$this->removeError($error_name);
	}

	/**
	 * @return int
	 */
	public function count() {
		return $this->getErrorsCount();
	}
}