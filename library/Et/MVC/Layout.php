<?php
namespace Et;

class MVC_Layout extends Data_Array {

	const MAIN_POSITION_NAME = "_main_";
	const LAYOUT_FILE_EXTENSION = ".phtml";
	const LAYOUT_PARTS_DIR = "parts";
	const LAYOUT_PART_TAG = "et_layout_part";
	const LAYOUT_POSITION_TAG = "et_layout_position";
	const MAIN_POSITION_TAG = "et_layout_main_position";
	const CONTENT_ORDER_FIRST = "first";
	const CONTENT_ORDER_LAST = "last";

	/**
	 * @var System_Dir
	 */
	protected $base_dir;

	/**
	 * @var bool
	 */
	protected $wrap_output_with_layout_path = ET_DEBUG_MODE;


	/**
	 * @var MVC_Layout_Content[]
	 */
	protected $_contents = array();

	/**
	 * @param string|System_Dir $base_dir
	 * @throws MVC_Layout_Exception
	 */
	function __construct($base_dir){

		if(!$base_dir instanceof System_Dir){
			$base_dir = System::getDir($base_dir);
		}

		if(!$base_dir->exists()){
			throw new MVC_Layout_Exception(
				"Layouts base directory '{$base_dir}' not exists",
				MVC_Layout_Exception::CODE_INVALID_LAYOUTS_DIRECTORY
			);
		}
		$this->base_dir = $base_dir;
	}

	/**
	 * @return System_Dir
	 */
	public function getBaseDir() {
		return $this->base_dir;
	}

	/**
	 * @param string $layout_name
	 *
	 * @throws MVC_Layout_Exception
	 */
	public static function checkLayoutNameFormat($layout_name){
		$layout_name = (string)$layout_name;
		if(!preg_match('~^[\w\-]+(/[\w\-]+)*$~', $layout_name)){
			throw new MVC_Layout_Exception(
				"Invalid layout name '{$layout_name}' format",
				MVC_Layout_Exception::CODE_INVALID_LAYOUT
			);
		}
	}

	/**
	 * @param string $layout_name
	 * @param bool $check_if_exists [optional]
	 *
	 * @return System_File
	 * @throws MVC_Layout_Exception
	 */
	function getLayoutFile($layout_name, $check_if_exists = true){
		$this->checkLayoutNameFormat($layout_name);
		$layout_path = $this->getBaseDir() . $layout_name . static::LAYOUT_FILE_EXTENSION;
		$layout_file = System::getFile($layout_path);

		if($check_if_exists && !$layout_file->exists()){
			throw new MVC_Layout_Exception(
				"Layout file '{$layout_path}' not exists",
				MVC_Layout_Exception::CODE_INVALID_LAYOUT
			);
		}

		return $layout_file;
	}

	/**
	 * @return array
	 */
	function getLayoutNamesList(){
		$output = array();
		$this->getBaseDir()->listFileNames(
			function($fn) use(&$output){
				if(preg_match('~^([\w\-]+)\.phtml$~', $fn, $m)){
					$output[] = $m[1];
				}
			}
		);
		return $output;
	}



	/**
	 * @param string $layout_name
	 *
	 * @return string
	 * @throws MVC_Layout_Exception
	 */
	function renderLayout($layout_name){

		$layout_file = $this->getLayoutFile($layout_name, true);

		try {
			ob_start();

			/** @noinspection PhpIncludeInspection */
			$content = include((string)$layout_file);
			if($content === false){
				Debug::triggerErrorOrLastError("include('{$layout_file}') failed");
			}

			$layout_output = ob_get_clean();
			$this->renderLayoutParts($layout_output);
			$this->renderLayoutPositions($layout_output);

		} catch(System_Exception $e){

			@ob_end_clean();
			throw new MVC_Layout_Exception(
				"Failed to render layout '{$layout_file}' - {$e->getMessage()}",
				MVC_Layout_Exception::CODE_RENDERING_FAILED,
				null,
				$e
			);

		}

		if($this->wrap_output_with_layout_path){
			$layout_path_relative = $layout_file->getRelativePath(null, "[root]/");
			$layout_output = "<!-- LAYOUT START '{$layout_path_relative}' -->\n" .
				trim($layout_output) . "\n" .
				"<!-- LAYOUT END '{$layout_path_relative}' -->";
		}

		return $layout_output;
	}

	/**
	 * @param string $part_name
	 * @throws MVC_Layout_Exception
	 * @return string
	 */
	protected function renderLayoutPart($part_name){
		$part_file = $this->getLayoutFile(static::LAYOUT_PARTS_DIR . "/{$part_name}", true);

		try {
			ob_start();

			/** @noinspection PhpIncludeInspection */
			$content = include((string)$part_file);
			if($content === false){
				Debug::triggerErrorOrLastError("include('{$part_file}') failed");
			}

			$part_output = ob_get_clean();
			$this->renderLayoutParts($part_output);
			//$this->renderLayoutPositions($part_output);

		} catch(System_Exception $e){

			@ob_end_clean();
			throw new MVC_Layout_Exception(
				"Failed to render layout part '{$part_file}' - {$e->getMessage()}",
				MVC_Layout_Exception::CODE_RENDERING_FAILED,
				null,
				$e
			);

		}

		if($this->wrap_output_with_layout_path){
			$layout_path_relative = $part_file->getRelativePath(null, "[root]/");
			$part_output = "<!-- LAYOUT PART START '{$layout_path_relative}' -->\n" .
				trim($part_output) . "\n" .
				"<!-- LAYOUT PART END '{$layout_path_relative}' -->";
		}

		$part_output = str_replace("\n", "\n\t", $part_output);

		return $part_output;

	}

	/**
	 * @param string $layout_output [reference]
	 */
	protected function renderLayoutParts(&$layout_output){
		preg_match_all(
			'~<'.static::LAYOUT_PART_TAG.'\s+name=[\'"]([\w\-]+)[\'"]\s*/>~s',
			$layout_output,
			$matches,
			PREG_SET_ORDER
		);

		foreach($matches as $m){
			list($search, $part_name) = $m;
			$content = $this->renderLayoutPart($part_name);
			$layout_output = str_replace($search, $content, $layout_output);
		}
	}


	/**
	 * @param string $layout_output [reference]
	 * @throws MVC_Layout_Exception
	 */
	protected function renderLayoutPositions(&$layout_output){

		if(!preg_match(
			'~<'.static::MAIN_POSITION_TAG.'.*/>~sU',
			$layout_output,
			$m
		)){
			throw new MVC_Layout_Exception(
				"Missing main position tag (<".static::MAIN_POSITION_TAG." />) in layout",
				MVC_Layout_Exception::CODE_INVALID_LAYOUT
			);
		}

		$positions = array(self::MAIN_POSITION_NAME => $m[0]);

		preg_match_all(
			'~<'.static::LAYOUT_POSITION_TAG.'\s+name=[\'"]([\w\-]+)[\'"](?:\s+title=(?:"[^"]+"|\'[^\']+\'))?\s*/>~s',
			$layout_output,
			$matches,
			PREG_SET_ORDER
		);


		foreach($matches as $m){
			$positions[$m[1]] = $m[0];
		}

		$contents = array();
		foreach($this->_contents as $content){
			$pos = $content->getPositionName();
			if(!$pos){
				$pos = static::MAIN_POSITION_NAME;
			}

			if(!isset($positions[$pos])){
				if($content->getPositionRequired()){
					continue;
				}
				$pos = static::MAIN_POSITION_NAME;
			}

			if(!isset($contents[$pos])){
				$contents[$pos] = array();
			}

			$contents[$pos][] = $content;
		}


		//todo: reorder

		foreach($positions as $position_name => $search){
			$content = "";
			if(isset($contents[$position_name])){
				/** @var $p MVC_Layout_Content */
				foreach($contents[$position_name] as $p){
					$content .= (string)$p . "\n";
				}
			}

			$layout_output = str_replace($search, $content, $layout_output);
		}

	}

	/**
	 * @param string $layout_name
	 * @return array
	 */
	public function getPositionTitles($layout_name){

		$layout_file = $this->getLayoutFile($layout_name, true);
		$content = $layout_file->getContent();

		$output = array();
		$output[self::MAIN_POSITION_NAME] = "-- Main position --";

		if(preg_match(
			'~<'.static::MAIN_POSITION_TAG.'\s+title=("[^"]+"|\'[^\']+\')\s*/>~s',
			$content,
			$m
		)){
			$output[self::MAIN_POSITION_NAME] = trim(substr($m[1], 1, strlen($m[1]) - 2));
		}

		preg_match_all(
			'~<'.static::LAYOUT_POSITION_TAG.'\s+name=[\'"]([\w\-]+)[\'"](?:\s+title=("[^"]+"|\'[^\']+\'))?\s*/>~s',
			$content,
			$matches,
			PREG_SET_ORDER
		);


		foreach($matches as $m){
			array_shift($m);
			$name = array_shift($m);
			$title = $name;
			if($m){
				$title = trim(substr($m[0], 1, strlen($m[0]) - 2));
			}
			$output[$name] = $title;
		}

		return $output;
	}

	/**
	 * @param boolean $wrap_output_with_layout_path
	 *
	 * @return static|\Et\MVC_Layout
	 */
	public function setWrapOutputWithLayoutPath($wrap_output_with_layout_path) {
		$this->wrap_output_with_layout_path = (bool)$wrap_output_with_layout_path;
		return $this;
	}

	/**
	 * @return boolean
	 */
	public function getWrapOutputWithLayoutPath() {
		return $this->wrap_output_with_layout_path;
	}


	/**
	 * @return array|mixed
	 */
	public function jsonSerialize() {
		return $this->getVisiblePropertiesValues();
	}

	/**
	 * @param string $path
	 * @return mixed
	 */
	function offsetGet($path){
		return $this->getHtmlSafeMixed($path);
	}

	/**
	 * @param string $path
	 * @return mixed
	 */
	function __get($path){
		return $this->getHtmlSafeMixed($path);
	}

	/**	 *
	 * @param string $path
	 * @param mixed $value
	 */
	function __set($path, $value){
		$this->set($path, $value);
	}

	/**
	 * @param string $path
	 * @return bool
	 */
	function __isset($path){
		return $this->exists($path);
	}

	/**
	 * @param string $layout_name
	 */
	function printLayout($layout_name){
		echo $this->renderLayout($layout_name);
	}

	/**
	 * @param string $content
	 * @param string $ID
	 * @return MVC_Layout_Content
	 */
	function addMainPositionContent($content, $ID = null){
		return $this->addContent($content, self::MAIN_POSITION_NAME, $ID);
	}

	/**
	 * @param string $content
	 * @param string $position_name
	 * @param string $ID [optional]
	 * @return MVC_Layout_Content
	 */
	function addContent($content, $position_name = self::MAIN_POSITION_NAME, $ID = null){
		if(!$ID){
			$ID = uniqid();
		}
		$instance = new MVC_Layout_Content($ID, $content, $position_name);
		$this->_contents[] = $instance;
		return $instance;
	}


	/**
	 * @return \Et\MVC_Layout_Content[]
	 */
	public function getContents() {
		return $this->_contents;
	}




}