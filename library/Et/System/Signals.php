<?php
namespace Et;
et_require("Object");
class System_Signals extends Object {

	/**
	 * @var System_Signals
	 */
	protected static $instance;

	/**
	 * @var callable[]
	 */
	protected $subscriptions = array();

	/**
	 * @var array[]
	 */
	protected $identifiers_pointers = array();

	/**
	 * @return System_Signals
	 */
	public static function getInstance(){
		if(!static::$instance){
			static::$instance = new static();
		}
		return static::$instance;
	}

	/**
	 * @param string $signal_name
	 * @param callable $signal_handler Callback like function(System_Signals_Signal $signal), if returns FALSE, further signal propagation will be stopped
	 * @return string Subscription identifier
	 */
	public function subscribe($signal_name, callable $signal_handler){
		if(!isset($this->identifiers_pointers[$signal_name])){
			$this->identifiers_pointers[$signal_name] = array();
		}

		$subscription_identifier = uniqid("{$signal_name}:");
		$this->subscriptions[$subscription_identifier] = $signal_handler;
		$this->identifiers_pointers[$signal_name][$subscription_identifier] = $subscription_identifier;

		return $subscription_identifier;
	}

	/** @noinspection SpellCheckingInspection */
	/**
	 * @param string $subscription_identifier
	 * @return bool
	 */
	public function unsubscribe($subscription_identifier){
		if(!isset($this->subscriptions[$subscription_identifier])){
			return false;
		}

		unset($this->subscriptions[$subscription_identifier]);

		list($signal_name) = explode(":", $subscription_identifier);
		unset($this->identifiers_pointers[$signal_name][$subscription_identifier]);

		return true;
	}

	/**
	 * @param System_Signals_Signal $signal
	 * @return int How many callbacks it passed
	 * @throws System_Signals_Exception
	 */
	public function publish(System_Signals_Signal $signal){
		$signal_name = $signal->getSignalName();
		if(!isset($this->identifiers_pointers[$signal_name])){
			return 0;
		}

		$passed = 0;
		foreach($this->identifiers_pointers[$signal_name] as $subscription_identifier){
			$callback = $this->subscriptions[$subscription_identifier];
			if(!is_callable($callback)){
				continue;
			}

			try {

				if(call_user_func($callback, $signal) === false){
					break;
				}

			} catch(\Exception $e){
				throw new System_Signals_Exception(
					"Failed to publish signal '{$signal_name}' - {$e->getMessage()}",
					System_Signals_Exception::CODE_PUBLISH_FAILED,
					array(
						"callback" => $callback
					),
					$e
				);
			}
			$passed++;
		}
		return $passed;
	}

	/**
	 * @param string $signal_name
	 * @throws System_Signals_Exception
	 */
	public static function checkSignalNameFormat($signal_name){
		if(!preg_match('~^[\w\-]+([/:][\w\-]+)*$~', $signal_name)){
			throw new System_Signals_Exception(
				"Invalid signal name format '{$signal_name}'",
				System_Signals_Exception::CODE_INVALID_NAME
			);
		}
	}

}