<?php
namespace Et;
et_require("Object");
abstract class System_Path extends Object implements \JsonSerializable {

	const PATH_SEPARATOR = "/";

	/**
	 * @var string
	 */
	protected $path;

	/**
	 * @var \SplFileInfo
	 */
	protected $info;

	/**
	 * @param string $path
	 */
	function __construct($path){
		$this->setPath($path);
	}

	/**
	 * @param string $file_path
	 *
	 * @return string
	 */
	public static function normalizeFilePath($file_path){
		return rtrim(str_replace(array("\\", DIRECTORY_SEPARATOR), static::PATH_SEPARATOR, $file_path), static::PATH_SEPARATOR);
	}

	/**
	 * @param string $dir_path
	 *
	 * @return string
	 */
	public static function normalizeDirPath($dir_path){
		return static::normalizeFilePath($dir_path) . static::PATH_SEPARATOR;
	}

	/**
	 * @param string $path
	 * @return System_Path
	 */
	public static function get($path){
		return new static($path);
	}

	/**
	 * @param string $path"/"
	 */
	abstract protected function setPath($path);

	/**
	 * @return string
	 */
	function getPath(){
		return $this->path;
	}

	/**
	 * Get parent directory instance
	 *
	 * @return System_Dir
	 */
	function getParentDir(){
		return System::getDir(dirname($this->path));
	}

	/**
	 * Get file/directory name
	 *
	 * @return string
	 */
	function getName(){
		return basename($this->path);
	}

	/**
	 * @return bool
	 */
	function exists(){
		return file_exists($this->path);
	}

	/**
	 * @return int
	 */
	abstract protected function getDefaultChmodMode();

	/**
	 * @param int|null $chmod_mode [optional] If NULL, ET_FS_FILES_CHMOD or ET_FS_DIRS_CHMOD is used, if -1 is passed, chmod() will be skipped
	 * @throws System_Exception
	 */
	function chmod($chmod_mode = null){

		$this->checkExists();

		if($chmod_mode === null){
			$chmod_mode = $this->getDefaultChmodMode();
		}

		$chmod_mode = (int)$chmod_mode;

		if($chmod_mode == -1){
			return;
		}

		try {
			if(!chmod($this->path, $chmod_mode)){
				Debug::triggerError(sprintf("chmod('%s', 0%o) failed", $this->path, $chmod_mode));
			}
		} catch(Debug_PHPError $e){
			throw new System_Exception(
				"chmod() failed - {$e->getMessage()}",
				System_Exception::CODE_CHMOD_FAILED,
				null,
				$e
			);
		}
	}

	/**
	 * @param null|string|int $owner_user [optional] if NULL, ET_FS_OWNER_USER is used. If empty string, chown() is skipped.
	 * @param null|string|int $owner_group [optional] if NULL, ET_FS_OWNER_GROUP is used. If empty string, chgrp() is skipped.
	 * @throws System_Exception
	 */
	function changeOwner($owner_user = null, $owner_group = null){
		$this->checkExists();

		if($owner_user === null){
			$owner_user = ET_DEFAULT_CHOWN_USER;
		}

		if($owner_group === null){
			$owner_group = ET_DEFAULT_CHOWN_GROUP;
		}

		try {

			if($owner_user !== "" && !chown($this->path, $owner_user)){
				$error = sprintf(
					"chown('%s', %s) failed",
					$this->path,
					is_string($owner_user) ? "'{$owner_user}'" : $owner_user
				);
				Debug::triggerErrorOrLastError($error);
			}

			if($owner_group !== "" && !chgrp($this->path, $owner_group)){
				$error = sprintf(
					"chgrp('%s', %s) failed",
					$this->path,
					is_string($owner_group) ? "'{$owner_group}'" : $owner_group
				);
				Debug::triggerErrorOrLastError($error);
			}

		} catch(Debug_PHPError $e){
			throw new System_Exception(
				"Failed to change owner - {$e->getMessage()}",
				System_Exception::CODE_CHANGE_OWNER_FAILED
			);
		}
	}

	/**
	 * @param int|null $chmod_mode [optional] If NULL, ET_FS_FILES_CHMOD or ET_FS_DIRS_CHMOD is used, if -1 is passed, chmod() will be skipped
	 * @param null|string|int $owner_user [optional] if NULL, ET_FS_OWNER_USER is used. If empty string, chown() is skipped.
	 * @param null|string|int $owner_group [optional] if NULL, ET_FS_OWNER_GROUP is used. If empty string, chgrp() is skipped.
	 * @throws System_Exception
	 */
	function setPermissions($chmod_mode = null, $owner_user = null, $owner_group = null){
		$this->chmod($chmod_mode);
		$this->changeOwner($owner_user, $owner_group);
	}

	/**
	 * @throws System_Exception
	 */
	function checkExists(){
		if(!$this->exists()){
			throw new System_Exception(
				"Path '{$this->path}' does not exist",
				System_Exception::CODE_NOT_FOUND
			);
		}
	}

	/**
	 * @return bool
	 */
	function isReadable(){
		return $this->exists() && is_readable($this->path);
	}

	/**
	 * @return bool
	 */
	function isWritable(){
		return $this->exists() && is_writable($this->path);
	}

	/**
	 * @throws System_Exception
	 */
	function checkIsReadable(){
		if(!$this->isReadable()){
			throw new System_Exception(
				"Path '{$this->path}' is not readable",
				System_Exception::CODE_NOT_READABLE
			);
		}
	}

	/**
	 * @throws System_Exception
	 */
	function checkIsWritable(){
		if(!$this->isWritable()){
			throw new System_Exception(
				"Path '{$this->path}' is not writable",
				System_Exception::CODE_NOT_WRITABLE
			);
		}
	}

	/**
	 * @param System_Path $relative_to [optional]
	 * @param string $root_prefix [optional]
	 * @return bool|string
	 */
	function getRelativePath(System_Path $relative_to = null, $root_prefix = ""){
		if(!$relative_to){
			$relative_to = new System_Dir(ET_BASE_PATH);
		}
		$relative_path = preg_replace('~^' . preg_quote((string)$relative_to) . "~", "", $this->path, 1, $count);
		if(!$count){
			return false;
		}
		return $root_prefix . $relative_path;
	}


	/**
	 * @return string
	 */
	function __toString(){
		return $this->getPath();
	}

	/**
	 * @return \SplFileInfo
	 */
	function getInfo(){
		if(!$this->info){
			$this->info = new \SplFileInfo($this->getPath());
		}
		return $this->info;
	}


	/**
	 * @return mixed|string
	 */
	public function jsonSerialize() {
		return $this->getPath();
	}
}