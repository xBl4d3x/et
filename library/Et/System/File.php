<?php
namespace Et;
et_require("System_Path");

class System_File extends System_Path {

	const FLAG_DELETE_PREFIX = "__deleted__";

	/**
	 * @var System_File_MimeType_Detector
	 */
	protected static $mime_type_detector;

	/**
	 * @param string $path
	 * @return System_File
	 */
	public static function get($path){
		return new static($path);
	}

	/**
	 * @return bool
	 */
	function exists(){
		return parent::exists() && is_file($this->path);
	}

	/**
	 * @param string $path
	 */
	protected function setPath($path){
		$this->path = $this->normalizeFilePath($path);
	}

	/**
	 * @return int
	 */
	protected function getDefaultChmodMode() {
		return ET_DEFAULT_FILES_CHMOD;
	}

	/**
	 * Get file extension or FALSE if does not have any
	 *
	 * @return string
	 */
	function getExtension(){
		return $this->getInfo()->getExtension();
	}

	/**
	 * @return string
	 */
	function getNameWithoutExtension(){
		$name = $this->getName();
		$extension = $this->getExtension();
		if($extension === ""){
			return $name;
		}
		return substr($name, -(strlen($extension) + 1));
	}

	/**
	 * @param string $open_mode [optional] Default: "r"
	 *
	 * @throws System_Exception
	 * @return \SplFileObject
	 */
	function getSplFileObject($open_mode = "r"){
		try {
			return new \SplFileObject($this->getPath(), $open_mode);
		} catch(\Exception $e){
			throw new System_Exception(
				"Failed to open file '{$this->getPath()}' - {$e->getMessage()}'",
				System_Exception::CODE_CANNOT_OPEN,
				null,
				$e
			);
		}

	}

	/**
	 * Delete file
	 *
	 * @throws System_Exception
	 */
	public function delete(){

		$this->checkExists();
		try {
			if(!unlink($this->path)){
				Debug::triggerError("unlink('{$this->path}') failed");
			}
		} catch(Debug_PHPError $e){
			if(file_exists($this->path)){
				throw new System_Exception(
					"Failed to delete file '{$this->path}' - {$e->getMessage()}",
					System_Exception::CODE_DELETE_FAILED,
					null,
					$e
				);
			}
		}
	}

	/**
	 * @return System_File
	 */
	public function flagDelete(){
		$file_name = static::FLAG_DELETE_PREFIX . $this->getNameWithoutExtension() . "__" . date("Y-m-d_H-i-s");
		$extension = $this->getExtension();
		if($extension !== ""){
			$file_name .= ".{$extension}";
		}
		return $this->moveTo(null, $file_name, true);
	}

	/**
	 * Create empty file
	 *
	 * @param bool $overwrite [optional] Default: TRUE
	 */
	public function create($overwrite = true){
		$this->writeContent("", $overwrite);
	}


	/**
	 * Write file content
	 *
	 * @param string $content
	 * @param bool $overwrite [optional] Default: TRUE
	 * @param bool $write_lock [optional]
	 *
	 * @throws System_Exception
	 */
	public function writeContent($content, $overwrite = true, $write_lock = false){

		$exists = $this->exists();
		if($exists && !$overwrite){
			throw new System_Exception(
				"File '{$this->path}' already exists and overwrite is disabled",
				System_Exception::CODE_ALREADY_EXISTS
			);
		}

		$dir = $this->getParentDir();
		if(!$dir->exists()){
			$dir->create();
		}

		try {
			if(file_put_contents($this->path, $content, $write_lock ? LOCK_EX : null) === false){
				Debug::triggerError("file_put_contents('{$this->path}', ...) failed");
			}
		} catch(Debug_PHPError $e){
			throw new System_Exception(
				"Failed write content to file '{$this->path}' - {$e->getMessage()}",
				System_Exception::CODE_WRITE_FAILED,
				null,
				$e
			);
		}

		if(!$exists){
			$this->setPermissions();
		}
	}

	/**
	 * Append file content
	 *
	 * @param string $content
	 * @param bool $write_lock [optional]
	 *
	 * @throws System_Exception
	 */
	public function appendContent($content, $write_lock = false){

		$exists = $this->exists();
		if(!$exists){
			$this->writeContent($content, true, $write_lock);
			return;
		}

		try {

			if(file_put_contents($this->path, $content, $write_lock ? FILE_APPEND | LOCK_EX : FILE_APPEND) === false){
				Debug::triggerErrorOrLastError(
					"file_put_contents('{$this->path}', ... , FILE_APPEND) failed"
				);
			}

		} catch(Debug_PHPError $e){

			throw new System_Exception(
				"Failed append content to file '{$this->path}' - {$e->getMessage()}",
				System_Exception::CODE_WRITE_FAILED,
				null,
				$e
			);

		}
	}


	/**
	 * @param int $offset [optional]
	 * @param null|int $max_length [optional]
	 *
	 * @throws System_Exception
	 * @return string
	 */
	public function getContent($offset = 0, $max_length = null){
		$this->checkIsReadable();
		try {
			if($offset){

				if($max_length){
					$output = file_get_contents($this->path, null, null, $offset, $max_length);
				} else {
					$output = file_get_contents($this->path, null, null, $offset);
				}

			} else {

				$output = file_get_contents($this->path);

			}

			if($output === false){
				Debug::triggerErrorOrLastError(
					"file_get_contents('{$this->path}') failed"
				);
			}
			return $output;

		} catch(Debug_PHPError $e){
			throw new System_Exception(
				"Failed read content of file '{$this->path}' - {$e->getMessage()}",
				System_Exception::CODE_READ_FAILED,
				null,
				$e
			);
		}
	}

	/**
	 * @return bool|mixed
	 * @throws System_Exception
	 */
	function includeContent(){
		$this->checkExists();
		try {

			/** @noinspection PhpIncludeInspection */
			$content = include($this->path);
			if($content === false){
				Debug::triggerError("include('{$this->path}') failed");
			}
			return $content;

		} catch(Debug_PHPError $e){
			throw new System_Exception(
				"Failed to include file '{$this->path}' - {$e->getMessage()}",
				System_Exception::CODE_INCLUDE_FAILED,
				null,
				$e
			);
		}
	}

	/**
	 * @return array
	 * @throws System_Exception
	 */
	function includeArrayContent(){
		$content = $this->includeContent();
		if(!is_array($content)){
			throw new System_Exception(
				"Failed to include file '{$this->path}' - does not contain array",
				System_Exception::CODE_INCLUDE_FAILED,
				array(
				     "content" => $content
				)
			);
		}
		return $content;
	}

	function writeSerializedData($content_to_serialize, $base64_encode = false, $write_lock = true){
		$content = serialize($content_to_serialize);
		if($base64_encode){
			$content = base64_encode($content);
		}
		$this->writeContent($content, true, $write_lock);
	}

	/**
	 * @param null|string $required_instance_class_name [optional]
	 * @param bool $decode_base64 [optional]
	 * @throws System_Exception
	 * @return object
	 */
	function getUnserializedData($required_instance_class_name = null, $decode_base64 = false){

		$content = $this->getContent();

		try {

			if($decode_base64){
				$content = base64_decode($content);
				if($content === false){
					Debug::triggerErrorOrLastError("base64_decode() returned FALSE");
				}
			}

			$unserialized = unserialize($content);
			if($unserialized === false && $content != serialize(false)){
				Debug::triggerErrorOrLastError("unserialize() returned FALSE");
			}

		} catch(Debug_PHPError $e){

			throw new System_Exception(
				"Failed to unserialize content of file '{$this->path}' - {$e->getMessage()}",
				System_Exception::CODE_INVALID_CONTENT,
				array(
					"serialized content" => $content
				)
			);

		}

		if($required_instance_class_name && !$unserialized instanceof $required_instance_class_name){
			throw new System_Exception(
				"Expected instance of '{$required_instance_class_name}' after unserialized content of file '{$this->path}', " .
				(is_object($unserialized)
					? "instance of " .get_class($unserialized). " returned instead"
					: "value of type " . gettype($unserialized) . " returned instead"
				),
				System_Exception::CODE_INVALID_CONTENT,
				array(
					"unserialized content" => $unserialized
				)
			);
		}

		return $unserialized;

	}

	/**
	 * @throws System_Exception
	 */
	function printContent(){
		$fp = $this->getSplFileObject("rb");
		$fp->fpassthru();
		unset($fp);
	}

	/**
	 * @return string
	 */
	function getRealPath(){
		return $this->normalizeFilePath(realpath($this->path));
	}

	/**
	 * @return string
	 * @throws System_Exception
	 */
	function getMimeType(){
		return System::getMimeTypeDetector()->getFileMimeType($this);
	}

	/**
	 * @return int
	 * @throws System_Exception
	 */
	function getSize(){
		$this->checkExists();
		return filesize($this);
	}

	/**
	 * @return string
	 */
	function getChecksum(){
		$this->checkExists();
		return md5_file($this->path);
	}

	/**
	 * @param null|string $target_units [optional] NULL = best unit, where output value is greater than 0
	 * @param int $max_fraction_digits [optional]
	 * @param Locales_Locale $locale [optional]
	 *
	 * @throws System_Exception
	 * @return string
	 */
	function getSizeLocalized($target_units = null, $max_fraction_digits = 3, Locales_Locale $locale = null){
		return Locales::getLocale($locale)->formatSize($this->getSize(), $target_units, $max_fraction_digits);
	}

	/**
	 * @param System_Dir $target_directory [optional]
	 * @param null|string $target_file_name [optional]
	 * @param bool $overwrite_if_exists [optional]
	 *
	 * @throws System_Exception
	 * @return System_File
	 */
	function copyTo(System_Dir $target_directory = null, $target_file_name = null, $overwrite_if_exists = true){

		$this->checkIsReadable();

		if(!$target_directory){
			$target_directory = $this->getParentDir();
		}

		if(!$target_directory->exists()){
			$target_directory->create();
		}

		if($target_file_name === null){
			$target_file_name = $this->getName();
		}

		$target_file = new System_File($target_directory->getPath() . $target_file_name);
		if(!$overwrite_if_exists && $target_file->exists()){
			throw new System_Exception(
				"Failed to copy file '{$this->path}' to '{$target_file->getPath()}' - target file already exists",
				System_Exception::CODE_COPY_FAILED
			);
		}

		try {

			if(!copy($this->getPath(), $target_file->getPath())){
				Debug::triggerError(
					"copy('{$this->getPath()}', '{$target_file->getPath()}') failed"
				);
			}

			return $target_file;

		} catch(Debug_PHPError $e){

			throw new System_Exception(
				"Failed to copy file '{$this->path}' to '{$target_file->getPath()}' - {$e->getMessage()}",
				System_Exception::CODE_COPY_FAILED,
				null,
				$e
			);

		}

	}

	/**
	 * @param System_Dir $target_directory [optional]
	 * @param null|string $target_file_name [optional]
	 * @param bool $overwrite_if_exists [optional]
	 *
	 * @throws System_Exception
	 * @return System_File
	 */
	function moveTo(System_Dir $target_directory = null, $target_file_name = null, $overwrite_if_exists = true){

		$this->checkIsReadable();

		if(!$target_directory){
			$target_directory = $this->getParentDir();
		}

		if(!$target_directory->exists()){
			$target_directory->create();
		}

		if($target_file_name === null){
			$target_file_name = $this->getName();
		}

		$target_file = new System_File($target_directory->getPath() . $target_file_name);
		if(!$overwrite_if_exists && $target_file->exists()){
			throw new System_Exception(
				"Failed to copy file '{$this->path}' to '{$target_file->getPath()}' - target file already exists",
				System_Exception::CODE_MOVE_FAILED
			);
		}

		try {

			if(!rename($this->getPath(), $target_file->getPath())){
				Debug::triggerError(
					"rename('{$this->getPath()}', '{$target_file->getPath()}') failed"
				);
			}

			return $target_file;

		} catch(Debug_PHPError $e){

			throw new System_Exception(
				"Failed to move file '{$this->path}' to '{$target_file->getPath()}' - {$e->getMessage()}",
				System_Exception::CODE_MOVE_FAILED,
				null,
				$e
			);

		}

	}

	/**
	 * @param null|string $file_name [optional]
	 * @param null|string $mime_type [optional]
	 * @param bool $force_download [optional]
	 * @param bool $exit_after_download [optional]
	 */
	function download($file_name = null, $mime_type = null, $force_download = false, $exit_after_download = true){

		$this->checkIsReadable();

		if($file_name === null){
			$file_name = $this->getName();
		}

		if(!$mime_type){
			$mime_type = $this->getMimeType();
		}

		$file_size = $this->getSize();

		@ob_end_clean(); //turn off output buffering to decrease cpu usage
		Http_Headers::checkHeadersNotSent();

		// required for IE, otherwise Content-Disposition may be ignored
		/** @noinspection SpellCheckingInspection */
		$output_compression = ini_get('zlib.output_compression');
		if($output_compression){
			/** @noinspection SpellCheckingInspection */
			@ini_set('zlib.output_compression', 'Off');
		}

		header("Content-Transfer-Encoding: binary");
		//header('Accept-Ranges: bytes');
		//TODO: podpora multipart downloads a download resume - viz http://w-shadow.com/blog/2007/08/12/how-to-force-file-download-with-php/
		header("Cache-control: private");
		header('Pragma: private');
		header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
		header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");

		header("Content-Length: {$file_size}");

		if($force_download){
			header("Content-Description: File Transfer");
			header("Content-Type: application/force-download");
			header("Content-Type: application/octet-stream", false);
			header("Content-Type: application/download", false);
			header("Content-Type: {$mime_type}", false);
			header("Content-Disposition: attachment; filename=\"{$file_name}\";");
		} else {
			header("Content-Type: {$mime_type}");
			header("Content-Disposition: inline; filename=\"{$file_name}\";");
		}

		$this->printContent();

		if($output_compression){
			/** @noinspection SpellCheckingInspection */
			ini_set('zlib.output_compression', $output_compression);
		}


		if($exit_after_download){
			Application::end();
		}
	}
}