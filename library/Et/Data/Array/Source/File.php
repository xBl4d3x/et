<?php
namespace Et;
et_require("Data_Array_Source_Abstract");
class Data_Array_Source_File extends Data_Array_Source_Abstract {

	const FORMAT_PHP = "php";
	const FORMAT_JSON = "json";
	const FORMAT_SERIALIZED = "serialized";

	/**
	 * @var \Et\System_File|null
	 */
	protected $file;

	/**
	 * @var string
	 */
	protected $data_format = self::FORMAT_SERIALIZED;

	/**
	 * @param null|string|\Et\System_File $file
	 * @param string $format
	 */
	function __construct($file = null, $format = self::FORMAT_SERIALIZED){
		if($file !== null){
			$this->setFile($file);
		}
		$this->setDataFormat($format);
	}

	/**
	 * @param string $data_format
	 */
	public function setDataFormat($data_format) {
		Debug_Assert::arrayContains($data_format, array(
			static::FORMAT_JSON,
			static::FORMAT_PHP,
			static::FORMAT_SERIALIZED
		));
		$this->data_format = $data_format;
	}

	/**
	 * @return string
	 */
	public function getDataFormat() {
		return $this->data_format;
	}

	/**
	 * @param \Et\System_File|string $file
	 */
	public function setFile($file) {
		if(!$file instanceof System_File){
			$file = new System_File((string)$file);
		}
		$this->file = $file;
	}

	/**
	 * @return \Et\System_File|null
	 */
	public function getFile() {
		return $this->file;
	}

	/**
	 * @throws Data_Array_Source_Exception
	 * @return array
	 */
	function loadData() {
		$file = $this->getFile();
		if(!$file){
			throw new Data_Array_Source_Exception(
				"No array source file specified",
				Data_Array_Source_Exception::CODE_INVALID_SOURCE
			);
		}

		if(!$file->isReadable()){
			throw new Data_Array_Source_Exception(
				"Array source file '{$file}' is not readable",
				Data_Array_Source_Exception::CODE_INVALID_SOURCE
			);
		}

		try {
			$data = $this->loadDataFromFile($file, $this->getDataFormat());
		} catch(\Exception $e){
			throw new Data_Array_Source_Exception(
				"Failed to load data from file '{$file}' - {$e->getMessage()}",
				Data_Array_Source_Exception::CODE_LOAD_FAILED,
				null,
				$e
			);
		}

		if(!is_array($data)){
			throw new Data_Array_Source_Exception(
				"Failed to load data from file '{$file}' - " . gettype($data) . " returned instead of array.",
				Data_Array_Source_Exception::CODE_LOAD_FAILED
			);
		}

		return $data;
	}

	/**
	 * @param System_File $file
	 * @param string $data_format
	 * @throws Data_Array_Source_Exception
	 * @return array|mixed
	 */
	protected function loadDataFromFile(System_File $file, $data_format){
		switch($data_format){
			case static::FORMAT_JSON:
				$content = $file->getContent();
				return json_decode($content, true);

			case static::FORMAT_SERIALIZED:
				$content = $file->getContent();
				return unserialize($content, true);

			case static::FORMAT_PHP:
				return $file->includeContent();

			default:
				throw new Data_Array_Source_Exception(
					"Unsupported data format '{$data_format}'",
					Data_Array_Source_Exception::CODE_STORE_FAILED
				);
		}
	}


	/**
	 * @param \Et\Data_Array $data
	 * @throws Data_Array_Source_Exception
	 */
	function storeData(Data_Array $data) {
		$file = $this->getFile();
		if(!$file){
			throw new Data_Array_Source_Exception(
				"No array source file specified",
				Data_Array_Source_Exception::CODE_INVALID_SOURCE
			);
		}

		try {
			$this->storeDataToFile($data, $file, $this->getDataFormat());
		} catch(\Exception $e){
			throw new Data_Array_Source_Exception(
				"Failed to store data to file '{$file}' - {$e->getMessage()}",
				Data_Array_Source_Exception::CODE_INVALID_SOURCE,
				null,
				$e
			);
		}
	}

	/**
	 * @param \Et\Data_Array $data
	 * @param System_File $file
	 * @param string $data_format
	 * @throws Data_Array_Source_Exception
	 */
	protected function storeDataToFile(Data_Array $data, System_File $file, $data_format){
		switch($data_format){
			case static::FORMAT_JSON:
				$encoded = json_encode($data->getData(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
				break;

			case static::FORMAT_SERIALIZED:
				$encoded = serialize($data->getData());
				break;

			case static::FORMAT_PHP:
				$encoded = "<?php\nreturn " . var_export($data->getData(), true) . ";\n";
				break;

			default:
				throw new Data_Array_Source_Exception(
					"Unsupported data format '{$data_format}'",
					Data_Array_Source_Exception::CODE_STORE_FAILED
				);
		}

		$file->writeContent($encoded);

	}

	/**
	 * @return bool
	 */
	function isReadable() {
		return $this->getFile() && $this->getFile()->isReadable();
	}

	/**
	 * @return bool
	 */
	function isWritable() {
		return $this->getFile() && $this->getFile()->isWritable();
	}
}