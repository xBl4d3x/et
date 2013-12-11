<?php
namespace Et;
et_require("Object");
class Locales_Timezone extends Object {

	/**
	 * @var \DateTimeZone
	 */
	protected $timezone;

	/**
	 * @param string|\DateTimeZone $timezone
	 * @throws Locales_Exception
	 */
	function __construct($timezone){
		if(!$timezone instanceof \DateTimeZone){
			try {
				$timezone = new \DateTimeZone((string)$timezone);
			} catch(\Exception $e){
				throw new Locales_Exception(
					"Failed to create \\DateTimeZone object - {$e->getMessage()}",
					Locales_Exception::CODE_INVALID_TIME_ZONE,
					null,
					$e
				);
			}
		}
		$this->timezone = $timezone;
	}

	/**
	 * @param string|\DateTimeZone $timezone
	 * @return Locales_Timezone
	 */
	public static function getInstance($timezone){
		return new static($timezone);
	}

	/**
	 * @return \DateTimeZone
	 */
	function getDateTimeZone(){
		return $this->timezone;
	}

	/**
	 * @return string
	 */
	function getTimezoneName(){
		return $this->timezone->getName();
	}


	/**
	 * @return string
	 */
	function __toString(){
		return $this->getTimezoneName();
	}
}