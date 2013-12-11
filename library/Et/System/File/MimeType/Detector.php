<?php
namespace Et;
class System_File_MimeType_Detector extends Object {

	const DEFAULT_MIME_TYPE = "application/octet-stream";

	/**
	 * @var bool
	 */
	protected $file_info_available = false;

	/**
	 * @var System_File
	 */
	protected $custom_magic_file_location = "";

	/**
	 * @var array
	 */
	protected $mime_detection_cache = array();

	/**
	 * @var System_File_MimeType_Detector_ExtensionsMap
	 */
	protected $extensions_to_mime_type_map;

	function __construct(){
		/** @noinspection SpellCheckingInspection */
		$this->file_info_available = extension_loaded("fileinfo");
	}

	/**
	 * @return System_File_MimeType_Detector
	 */
	function clearMimeDetectionCache(){
		$this->mime_detection_cache = array();
		return $this;
	}

	/**
	 * @param System_File $file
	 *
	 * @return string
	 */
	function getFileMimeType(System_File $file){

		$file->checkExists();

		$file_path = realpath($file->getPath());
		$mod_time = filemtime($file_path);
		$cache_key = md5("{$file_path}:{$mod_time}");
		if(isset($this->mime_detection_cache[$cache_key])){
			return $this->mime_detection_cache[$cache_key];
		}

		$extension = $file->getExtension();
		$suggested_type = self::DEFAULT_MIME_TYPE;

		if($extension){
			$suggested_type = $this->getMimeTypeByExtension($extension);
		}

		$mime_type = false;
		if($this->file_info_available){
			$mime_type = $this->getMimeTypeFromFileInfo($file_path);
		}

		if(!$mime_type || $mime_type == "application/octet-stream"){

			if($this->custom_magic_file_location){
				$mt = @exec("file -m ".escapeshellarg($this->custom_magic_file_location)." -bi ".escapeshellarg($file_path));
			} else {
				$mt = @exec("file -bi ".escapeshellarg($file_path) );
			}

			if($mt){
				$mime_type = $mt;
			}
		}

		if(!$mime_type || $mime_type == "application/octet-stream" && function_exists("mime_content_type")){
			$mt = @mime_content_type($file_path);
			if($mt){
				$mime_type = $mt;
			}
		}

		// ZIP-based files
		if($mime_type == "application/zip" && $suggested_type != self::DEFAULT_MIME_TYPE && $suggested_type != $mime_type){
			$mime_type = $suggested_type;
		}

		if(!$mime_type){
			 $mime_type = $suggested_type;
		}

		$this->mime_detection_cache[$cache_key] = $mime_type;

		return $mime_type;
	}

	/**
	 * @param string $file_path
	 *
	 * @return bool|string
	 */
	protected function getMimeTypeFromFileInfo($file_path){
		$info = null;
		if($this->custom_magic_file_location){
			$info = @finfo_open(FILEINFO_MIME, (string)$this->custom_magic_file_location);
		}

		if(!$info){
			$info = @finfo_open(FILEINFO_MIME);
		}

		if(!$info){
			/** @noinspection SpellCheckingInspection */
			$magic_file = @ini_get('mime_magic.magicfile');
			if($magic_file && @file_exists($magic_file)){
				$info = @finfo_open(FILEINFO_MIME, $magic_file);
			}
		}

		if(!$info){
			return false;
		}

		$mime_type = @finfo_file($info, $file_path);
		@finfo_close($info);

		if(!$mime_type){
			return false;
		}

		list($mime_type) = explode(";", $mime_type);

		return $mime_type;
	}

	/**
	 * @return System_File_MimeType_Detector_ExtensionsMap
	 */
	function getExtensionsToMimeTypeMap(){
		if(!$this->extensions_to_mime_type_map){

			$this->extensions_to_mime_type_map = new System_File_MimeType_Detector_ExtensionsMap();
		}
		return $this->extensions_to_mime_type_map;
	}

	/**
	 * @param System_File_MimeType_Detector_ExtensionsMap $extensions_to_mime_type_map
	 */
	public function setExtensionsToMimeTypeMap(System_File_MimeType_Detector_ExtensionsMap $extensions_to_mime_type_map) {
		$this->extensions_to_mime_type_map = $extensions_to_mime_type_map;
	}



	/**
	 * @param array $mime_types_by_extension
	 * @param bool $merge [optional]
	 *
	 * @throws Debug_Assert_Exception when invalid argument is passed
	 */
	public function setMimeTypesByExtension(array $mime_types_by_extension, $merge = true) {
		$this->getExtensionsToMimeTypeMap()->setMimeTypesByExtension($mime_types_by_extension, $merge);
	}

	/**
	 * @return array
	 */
	public function getMimeTypesByExtension() {
		return $this->getExtensionsToMimeTypeMap()->getMimeTypesByExtension();
	}

	/**
	 * @param string $extension
	 * @param string $mime_type
	 *
	 * @throws Debug_Assert_Exception
	 */
	function setMimeTypeByExtension($extension, $mime_type){
		$this->getExtensionsToMimeTypeMap()->setMimeTypeByExtension($extension, $mime_type);
	}

	/**
	 * @param $extension
	 *
	 * @return string
	 */
	function getMimeTypeByExtension($extension){
		return $this->getExtensionsToMimeTypeMap()->getMimeTypeByExtension($extension);
	}

	/**
	 * @param System_File $custom_magic_file_location
	 */
	public function setCustomMagicFileLocation(System_File $custom_magic_file_location = null) {
		if($custom_magic_file_location){
			$custom_magic_file_location->checkExists();
		}
		$this->custom_magic_file_location = $custom_magic_file_location;
	}

	/**
	 * @return System_File|null
	 */
	public function getCustomMagicFileLocation() {
		return $this->custom_magic_file_location;
	}




}