<?php
namespace Et;
trait Object_MagicGetTrait {
	/**
	 * Avoid getting undefined properties (probably error in code .. )
	 *
	 * @param string $property
	 *
	 * @throws Object_Exception
	 */
	public function __get($property){
		et_require("Object_Exception");
		if(!property_exists($this, $property)){
			throw new Object_Exception(
				"Property ".get_class($this)."->{$property} does not exist",
				Object_Exception::CODE_UNKNOWN_PROPERTY_ACCESS
			);
		}

		throw new Object_Exception(
			"Cannot get ".get_class($this)."->{$property} property value - permission denied",
			Object_Exception::CODE_PROTECTED_PROPERTY_ACCESS
		);
	}
}