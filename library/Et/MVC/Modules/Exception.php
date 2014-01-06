<?php
namespace Et;
class MVC_Modules_Exception extends Exception {

	const CODE_INVALID_MODULE_ID = 10;
	const CODE_MODULE_NOT_EXIST = 20;
	const CODE_MODULE_NOT_INSTALLED = 30;
	const CODE_MODULE_NOT_ENABLED = 40;
	const CODE_INVALID_METADATA = 50;
	const CODE_INVALID_CONFIG = 60;
	const CODE_INVALID_MODULE = 70;
	const CODE_INVALID_INSTALLER = 80;
	const CODE_INVALID_AUTH_ACTION = 90;
	const CODE_INSTALLER_FAILURE = 100;

	/**
	 * Exception error codes human readable labels
	 *
	 * @var array
	 */
	protected static $error_codes_labels = array(
		self::CODE_INVALID_MODULE_ID => "Invalid module ID",
		self::CODE_MODULE_NOT_EXIST => "Module not exist",
		self::CODE_MODULE_NOT_INSTALLED => "Module not installed",
		self::CODE_MODULE_NOT_ENABLED => "Module not enabled",
		self::CODE_INVALID_METADATA => "Invalid metadata",
		self::CODE_INVALID_CONFIG => "Invalid config",
		self::CODE_INVALID_MODULE => "Invalid module",
		self::CODE_INVALID_INSTALLER => "Invalid installer",
		self::CODE_INVALID_AUTH_ACTION => "Invalid auth action",
		self::CODE_INSTALLER_FAILURE => "Module installer failure"
	);
}
