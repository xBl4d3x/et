<?php
namespace Et;
et_require("Exception");

class System_Exception extends Exception {

	const CODE_NOT_FOUND = 10;
	const CODE_NOT_READABLE = 20;
	const CODE_NOT_WRITABLE = 30;
	const CODE_CANNOT_OPEN = 40;
	const CODE_CANNOT_CREATE = 50;
	const CODE_CHMOD_FAILED = 60;
	const CODE_INVALID_PATH = 70;
	const CODE_ALREADY_EXISTS = 80;
	const CODE_READ_FAILED = 90;
	const CODE_WRITE_FAILED = 100;
	const CODE_DELETE_FAILED = 110;
	const CODE_CHANGE_OWNER_FAILED = 120;
	const CODE_INCLUDE_FAILED = 130;
	const CODE_INVALID_CONTENT_TYPE = 140;
	const CODE_INVALID_CONTENT = 150;
	const CODE_GET_FILE_SIZE_FAILED = 160;
	const CODE_UNKNOWN_FILE_EXTENSION = 170;
	const CODE_UNKNOWN_FILE_MIME_TYPE = 180;
	const CODE_DOWNLOAD_FAILED = 190;
	const CODE_PRINT_FILE_FAILED = 200;
	const CODE_MOVE_FAILED = 210;
	const CODE_COPY_FAILED = 220;

	/**
	 * Exception error codes human readable labels
	 *
	 * @var array
	 */
	protected static $error_codes_labels = array(
		self::CODE_NOT_FOUND              => "Not found",
		self::CODE_NOT_READABLE           => "Not readable",
		self::CODE_NOT_WRITABLE           => "Not writable",
		self::CODE_CANNOT_OPEN            => "Cannot open",
		self::CODE_CANNOT_CREATE          => "Cannot create",
		self::CODE_CHMOD_FAILED           => "Chmod failed",
		self::CODE_INVALID_PATH           => "Invalid path",
		self::CODE_ALREADY_EXISTS         => "Already exists",
		self::CODE_READ_FAILED            => "Read failed",
		self::CODE_WRITE_FAILED           => "Write failed",
		self::CODE_DELETE_FAILED          => "Delete failed",
		self::CODE_CHANGE_OWNER_FAILED    => "Change owner failed",
		self::CODE_INCLUDE_FAILED         => "Include failed",
		self::CODE_INVALID_CONTENT_TYPE   => "Invalid content type",
		self::CODE_INVALID_CONTENT        => "Invalid content",
		self::CODE_GET_FILE_SIZE_FAILED   => "Get file size failed",
		self::CODE_UNKNOWN_FILE_EXTENSION => "Unknown file extension",
		self::CODE_UNKNOWN_FILE_MIME_TYPE => "Unknown file mime type",
		self::CODE_DOWNLOAD_FAILED        => "Download failed",
		self::CODE_PRINT_FILE_FAILED      => "Print file failed",
		self::CODE_MOVE_FAILED            => "Move failed",
		self::CODE_COPY_FAILED            => "Copy failed"
	);

}