<?php
namespace Et;
class Locales_Timezone extends \DateTimeZone {


	/**
	 * @param string|\DateTimeZone $timezone
	 * @throws Locales_Exception
	 */
	function __construct($timezone){
		try {
			parent::__construct((string)$timezone);
		} catch(\Exception $e){
			throw new Locales_Exception(
				"Failed to create timezone instance - {$e->getMessage()}",
				Locales_Exception::CODE_INVALID_TIME_ZONE
			);
		}
	}

	/**
	 * @param string $timezone
	 * @return \Et\Locales_Timezone|static
	 */
	public static function getInstance($timezone){
		return new static($timezone);
	}

	/**
	 * @return string
	 */
	function __toString(){
		return $this->getName();
	}
}