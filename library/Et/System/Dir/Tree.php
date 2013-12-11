<?php
namespace Et;
et_require("Object");

class System_Dir_Tree extends Object implements \JsonSerializable {

	/**
	 * @var callable
	 */
	protected $files_filter;

	/**
	 * @var callable
	 */
	protected $dirs_filter;

	/**
	 * @var System_Dir
	 */
	protected $dir;

	/**
	 * @var System_File[]
	 */
	protected $files;

	/**
	 * @var System_Dir[]
	 */
	protected $subdirectories;

	/**
	 * @var System_Dir_Tree
	 */
	protected $parent;

	/**
	 * @var System_Dir_Tree[]
	 */
	protected $children;

	/**
	 * @var int
	 */
	protected $depth = 0;

	/**
	 * @var string
	 */
	protected $relative_path = "/";

	/**
	 * @param System_Dir $dir
	 * @param callable $dirs_filter [optional]
	 * @param callable $files_filter [optional]
	 * @param System_Dir_Tree $parent [optional[
	 */
	function __construct(System_Dir $dir, callable $dirs_filter = null, callable $files_filter = null, System_Dir_Tree $parent = null){
		$this->dir = $dir;
		$this->dirs_filter = $dirs_filter;
		$this->files_filter = $files_filter;
		$this->parent = $parent;
		if($parent){
			$this->depth = $parent->getDepth() + 1;
			$this->relative_path = $parent->getRelativePath() . $dir->getName() . "/";
		}
	}

	/**
	 * @return string
	 */
	function getRelativePath(){
		return $this->relative_path;
	}

	/**
	 * @return System_Dir_Tree
	 */
	function getRoot(){
		if(!$this->parent){
			return $this;
		}
		return $this->parent->getRoot();
	}

	/**
	 * @return int
	 */
	function getDepth(){
		return $this->depth;
	}

	/**
	 * @return System_Dir
	 */
	public function getDir() {
		return $this->dir;
	}

	/**
	 * @return callable|null
	 */
	public function getDirsFilter() {
		return $this->dirs_filter;
	}

	/**
	 * @return System_File[]
	 */
	public function getFiles() {
		if($this->files === null){
			$this->files = $this->getDir()->listFiles($this->getFilesFilter());
		}
		return $this->files;
	}

	/**
	 * @return callable|null
	 */
	public function getFilesFilter() {
		return $this->files_filter;
	}

	/**
	 * @return System_Dir[]
	 */
	public function getSubdirectories() {
		if($this->subdirectories === null){
			$this->subdirectories = $this->getDir()->listDirs($this->getDirsFilter());
		}
		return $this->subdirectories;
	}

	/**
	 * @return System_Dir_Tree
	 */
	function getParent(){
		return $this->parent;
	}

	/**
	 * @return System_Dir_Tree[]
	 */
	function getChildren(){
		if($this->children === null){
			$dirs = $this->getSubdirectories();
			$this->children = array();
			foreach($dirs as $dir_name => $dir){
				$this->children[$dir_name] = new static($dir, $this->getDirsFilter(), $this->getFilesFilter(), $this);
			}
		}
		return $this->children;
	}

	/**
	 * @return string
	 */
	function __toString(){
		return (string)$this->getDir();
	}

	/**
	 * @return array
	 */
	function toArray(){
		$children = $this->getChildren();
		$files = $this->getFiles();
		$output = array(
			"name" => $this->getDir()->getName(),
			"path" => (string)$this->getDir(),
			"depth" => $this->getDepth(),
			"relative_path" => $this->getRelativePath(),
			"type" => "directory",
			"files" => array(),
			"dirs" => array()
		);

		foreach($children as $dir_name => $child){
			$output["dirs"][$dir_name] = $child->toArray();
		}

		foreach($files as $file_name => $file){
			$output["files"][$file_name] = array(
				"name" => $file_name,
				"path" => (string)$file,
				"relative_path" => $this->getRelativePath() . $file_name,
				"type" => "file",
				"depth" => $this->getDepth() + 1,
			);
		}

		return $output;
	}

	/**
	 * (PHP 5 &gt;= 5.4.0)<br/>
	 * Specify data which should be serialized to JSON
	 * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
	 * @return mixed data which can be serialized by <b>json_encode</b>,
	 * which is a value of any type other than a resource.
	 */
	public function jsonSerialize() {
		return $this->toArray();
	}
}