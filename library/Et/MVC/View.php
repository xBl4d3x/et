<?php
namespace Et;

class MVC_View extends Data_Array {

	const VIEW_FILE_EXTENSION = ".phtml";
	const MODULES_VIEWS_DIR = "views";
	const VIEW_PARTS_DIR = "parts";
	const VIEW_PART_TAG = "et_view_part";

	/**
	 * @var System_Dir
	 */
	protected $base_dir;

	/**
	 * @var bool
	 */
	protected $wrap_output_with_view_path = ET_DEBUG_MODE;

	/**
	 * @var string
	 */
	protected $module_ID;

	/**
	 * @var MVC_Modules_Module_Metadata
	 */
	protected $module_metadata;

	/**
	 * @var MVC_Modules_Module
	 */
	protected $module_instance;

	/**
	 * @var array
	 */
	protected $forms = array();

	/**
	 * @param MVC_Modules_Module $module
	 * @throws MVC_View_Exception
	 */
	function __construct(MVC_Modules_Module $module){

		$this->module_instance = $module;
		$this->module_ID = $module->getModuleID();
		$this->module_metadata = $module->getModuleMetadata();

		$base_dir = System::getDir($module->getModuleDirectory() . static::MODULES_VIEWS_DIR . "/");
		if(!$base_dir->exists()){
			throw new MVC_View_Exception(
				"Views base directory '{$base_dir}' not exists",
				MVC_View_Exception::CODE_INVALID_BASE_DIR
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
	 * @param string $view_name
	 *
	 * @throws MVC_View_Exception
	 */
	public static function checkViewNameFormat($view_name){
		$view_name = (string)$view_name;
		if(!preg_match('~^[\w\-]+(/[\w\-]+)*$~', $view_name)){
			throw new MVC_View_Exception(
				"Invalid view name '{$view_name}' format",
				MVC_View_Exception::CODE_INVALID_VIEW_NAME
			);
		}
	}

	/**
	 * @param string $view_name
	 * @param bool $check_if_exists [optional]
	 *
	 * @return System_File
	 * @throws MVC_View_Exception
	 */
	function getViewFile($view_name, $check_if_exists = true){
		$this->checkViewNameFormat($view_name);
		$view_path = $this->getBaseDir() . $view_name . static::VIEW_FILE_EXTENSION;
		$view_file = System::getFile($view_path);

		if($check_if_exists && !$view_file->exists()){
			throw new MVC_View_Exception(
				"View file '{$view_path}' not exists",
				MVC_View_Exception::CODE_INVALID_VIEW_NAME
			);
		}

		return $view_file;
	}

	/**
	 * @return array
	 */
	function getViewNamesList(){
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
	 * @param string $view_output
	 * @param System_File $view_file
	 *
	 * @throws MVC_View_Exception
	 */
	protected function wrapViewOutputWithViewPath(&$view_output, System_File $view_file){

		$view_path_relative = $view_file->getRelativePath(null, "[root]/");
		$view_output = "<!-- VIEW START '{$view_path_relative}' -->\n" .
		               trim($view_output) . "\n" .
		               "<!-- VIEW END '{$view_path_relative}' -->";
	}


	/**
	 * @param string $view_name
	 *
	 * @return string
	 * @throws MVC_View_Exception
	 */
	function renderView($view_name){

		$view_file = $this->getViewFile($view_name, true);

		try {
			ob_start();

			/** @noinspection PhpIncludeInspection */
			$content = include((string)$view_file);
			if($content === false){
				Debug::triggerErrorOrLastError("include('{$view_file}') failed");
			}

			$view_output = ob_get_clean();
			$this->renderViewParts($view_output);

		} catch(System_Exception $e){

			@ob_end_clean();
			throw new MVC_View_Exception(
				"Failed to render view '{$view_file}' - {$e->getMessage()}",
				MVC_View_Exception::CODE_RENDERING_FAILED,
				null,
				$e
			);

		}

		if($this->wrap_output_with_view_path){
			$this->wrapViewOutputWithViewPath($view_output, $view_file);
		}

		return $view_output;
	}

	/**
	 * @param string $view_output [reference]
	 */
	protected function renderViewParts(&$view_output){
		preg_match_all(
			'~<'.static::VIEW_PART_TAG.'\s+name=[\'"]([\w\-]+)[\'"]\s*/>~s',
			$view_output,
			$matches,
			PREG_SET_ORDER
		);

		foreach($matches as $m){
			list($search, $part_name) = $m;
			$content = $this->renderView(static::VIEW_PARTS_DIR . "/{$part_name}");
			$view_output = str_replace($search, $content, $view_output);
		}
	}

	/**
	 * @param boolean $wrap_output_with_view_path
	 *
	 * @return static|\Et\MVC_View
	 */
	public function setWrapOutputWithViewPath($wrap_output_with_view_path) {
		$this->wrap_output_with_view_path = (bool)$wrap_output_with_view_path;
		return $this;
	}

	/**
	 * @return boolean
	 */
	public function getWrapOutputWithViewPath() {
		return $this->wrap_output_with_view_path;
	}


	/**
	 * @return array|mixed
	 */
	public function jsonSerialize() {
		return $this->_getVisiblePropertiesValues();
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
	 * @param string $view_name
	 */
	function printView($view_name){
		echo $this->renderView($view_name);
	}
}