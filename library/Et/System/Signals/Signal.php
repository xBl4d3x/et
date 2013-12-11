<?php
namespace Et;
et_require("Object");
class System_Signals_Signal extends Object {

	/**
	 * @var string
	 */
	protected $signal_name;

	/**
	 * @var object
	 */
	protected $signal_source;

	/**
	 * @param string $signal_name
	 * @param \Et\Object $signal_source [optional]
	 */
	function __construct($signal_name, Object $signal_source = null){
		System_Signals::checkSignalNameFormat($signal_name);
		$this->signal_name = $signal_name;
		$this->signal_source = $signal_source;
	}

	/**
	 * @return string
	 */
	public function getSignalName() {
		return $this->signal_name;
	}

	/**
	 * @param \Et\Object $signal_source
	 */
	public function setSignalSource(Object $signal_source = null) {
		$this->signal_source = $signal_source;
	}

	/**
	 * @return \Et\Object|null
	 */
	public function getSignalSource() {
		return $this->signal_source;
	}
}