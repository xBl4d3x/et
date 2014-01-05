<?php
namespace Et;
et_require("System_Path");

class System_Dir extends System_Path {

	/**
	 * @param string $path
	 * @return System_Dir
	 */
	public static function get($path){
		return new static($path);
	}

	/**
	 * @return bool
	 */
	function exists(){
		return parent::exists() && is_dir($this->path);
	}

	/**
	 * @param string $path
	 */
	protected function setPath($path){
		$this->path = $this->normalizeDirPath($path);
	}

	/**
	 * @return int
	 */
	protected function getDefaultChmodMode() {
		return ET_DEFAULT_DIRS_CHMOD;
	}


	/**
	 * Create empty directory
	 *
	 * @param bool $remove_if_exists [optional] Default: FALSE
	 *
	 * @throws System_Exception
	 */
	public function create($remove_if_exists = false){
		if($this->exists()){
			if(!$remove_if_exists){
				return;
			}
			$this->delete();
		}

		$parent_dir = $this->getParentDir();
		if(!$parent_dir->exists()){
			if($parent_dir->getPath() == $this->getPath()){
				throw new System_Exception(
					"Failed to create directory '{$this->path}' - root reached?",
					System_Exception::CODE_CANNOT_CREATE
				);
			}
			$parent_dir->create(false);
		}

		try {

			if(!mkdir($this->path)){
				/** @noinspection SpellCheckingInspection */
				Debug::triggerErrorOrLastError("mkdir('{$this->path}') failed");
			}

			$this->setPermissions();

		} catch(Debug_PHPError $e){

			throw new System_Exception(
				"Failed to create directory '{$this->path}' - {$e->getMessage()}",
				System_Exception::CODE_CANNOT_CREATE
			);

		}
	}

	/**
	 * Remove directory
	 *
	 * @throws System_Exception
	 */
	public function delete(){
		try {

			$this->passThrough(
				function(System_File $file){
					$file->delete();
				},
				function(System_Dir $dir){
					$dir->delete();
				}
			);

			if(!rmdir($this->path)){
				/** @noinspection SpellCheckingInspection */
				Debug::triggerErrorOrLastError("rmdir('{$this->path}') failed");
			}

		} catch(Debug_PHPError $e){
			if(file_exists($this->path)){
				throw new System_Exception(
					"Failed to delete directory '{$this->path}' - {$e->getMessage()}",
					System_Exception::CODE_DELETE_FAILED
				);
			}
		}
	}


	/**
	 * @return \Directory
	 * @throws System_Exception
	 */
	function open(){

		$this->checkIsReadable();
		try {

			$dir = dir($this->path);
			if(!$dir instanceof \Directory){
				Debug::triggerErrorOrLastError("dir('{$this->path}') failed");
			}
			return $dir;

		} catch(Debug_PHPError $e){
			throw new System_Exception(
				"Failed to open directory '{$this->path}' - {$e->getMessage()}",
				System_Exception::CODE_CANNOT_OPEN
			);
		}
	}


	/**
	 * @param null|callable $file_callback [optional] Callback called to each file, arguments order: $file_instance
	 * @param null|callable $dir_callback [optional] Callback called to each directory, arguments order: $dir_instance
	 */
	public function passThrough(callable $file_callback = null, callable $dir_callback = null){
		if(!$file_callback && !$dir_callback){
			$this->checkExists();
			return;
		}

		$handle = $this->open();

		while(($fn= $handle->read()) !== false){
			if($fn == "." || $fn == ".."){
				continue;
			}

			$fp = "{$this->path}{$fn}";
			if(is_dir($fp)){
				if($dir_callback){
					$dir_callback(new System_Dir($fp));
				}
			} else {
				if($file_callback){
					$file_callback(new System_File($fp));
				}
			}
		}

		$handle->close();
	}

	/**
	 * chmod() files in directory
	 *
	 * @param null|int|bool $files_chmod [optional]
	 * @param callable $file_filter [optional] Callback like function(System_File $file) returning TRUE chmod() should be applied or FALSE if not
	 */
	public function chmodFiles($files_chmod = null, callable $file_filter = null){
		$this->passThrough(
			function(System_File $file) use ($file_filter, $files_chmod){
				if(!$file_filter || $file_filter($file)){
					$file->chmod($files_chmod);
				}	
			}
		);
	}

	/**
	 * chmod() subdirectories in directory
	 *
	 * @param null|int|bool $dirs_chmod [optional]
	 * @param callable $dir_filter [optional] Callback like function(System_Dir $dir) returning TRUE chmod() should be applied or FALSE if not
	 */
	public function chmodSubdirectories($dirs_chmod = null, callable $dir_filter = null){
		$this->passThrough(
			null, 
			function(System_Dir $dir) use ($dir_filter, $dirs_chmod){
				if(!$dir_filter || $dir_filter($dir)){
					$dir->chmod($dirs_chmod);
				}
			}
		);
	}

	/**
	 * chmod() directory, subdirectories and files
	 * 
	 * @param null|int|bool $dirs_chmod [optional]
	 * @param null|int|bool $files_chmod [optional]
	 * @param callable $dir_filter [optional] Callback like function(System_Dir $dir) returning TRUE chmod() should be applied or FALSE if not
	 * @param callable $file_filter [optional] Callback like function(System_File $file) returning TRUE chmod() should be applied or FALSE if not
	 */
	function chmodRecursive($dirs_chmod = null, $files_chmod = null, callable $dir_filter = null, callable $file_filter = null){
		$this->chmod($dirs_chmod);
		$this->passThrough(
			function(System_File $file) use ($file_filter, $files_chmod){
				if(!$file_filter || $file_filter($file)){
					$file->chmod($files_chmod);
				}
			},
			function(System_Dir $dir) use ($dir_filter, $dirs_chmod){
				if(!$dir_filter || $dir_filter($dir)){
					$dir->chmod($dirs_chmod);
				}
			}
		);
	}


	/**
	 * chown() files in directory
	 *
	 * @param null|string|int $owner_user [optional] if NULL, ET_FS_OWNER_USER is used. If empty string, chown() is skipped.
	 * @param null|string|int $owner_group [optional] if NULL, ET_FS_OWNER_GROUP is used. If empty string, chgrp() is skipped.
	 * @param callable $file_filter [optional] Callback like function(System_File $file) returning TRUE chown() should be applied or FALSE if not
	 */
	public function changeFilesOwner($owner_user = null, $owner_group = null, callable $file_filter = null){
		$this->passThrough(
			function(System_File $file) use ($file_filter, $owner_user, $owner_group){
				if(!$file_filter || $file_filter($file)){
					$file->changeOwner($owner_user, $owner_group);
				}
			}
		);
	}

	/**
	 * chown() subdirectories in directory
	 *
	 * @param null|string|int $owner_user [optional] if NULL, ET_FS_OWNER_USER is used. If empty string, chown() is skipped.
	 * @param null|string|int $owner_group [optional] if NULL, ET_FS_OWNER_GROUP is used. If empty string, chgrp() is skipped.
	 * @param callable $dir_filter [optional] Callback like function(System_Dir $dir) returning TRUE chown() should be applied or FALSE if not
	 */
	public function changeSubdirectoriesOwner($owner_user = null, $owner_group = null, callable $dir_filter = null){
		$this->passThrough(
			null,
			function(System_Dir $dir) use ($dir_filter, $owner_user, $owner_group){
				if(!$dir_filter || $dir_filter($dir)){
					$dir->changeOwner($owner_user, $owner_group);
				}
			}
		);
	}

	/**
	 * chown() directory, subdirectories and files
	 *
	 * @param null|string|int $owner_user [optional] if NULL, ET_FS_OWNER_USER is used. If empty string, chown() is skipped.
	 * @param null|string|int $owner_group [optional] if NULL, ET_FS_OWNER_GROUP is used. If empty string, chgrp() is skipped.
	 * @param callable $dir_filter [optional] Callback like function(System_Dir $dir) returning TRUE chown() should be applied or FALSE if not
	 * @param callable $file_filter [optional] Callback like function(System_File $file) returning TRUE chown() should be applied or FALSE if not
	 */
	function changeOwnerRecursive($owner_user = null, $owner_group = null, callable $dir_filter = null, callable $file_filter = null){
		$this->changeOwner($owner_user, $owner_group);
		$this->passThrough(
			function(System_File $file) use ($file_filter, $owner_user, $owner_group){
				if(!$file_filter || $file_filter($file)){
					$file->changeOwner($owner_user, $owner_group);
				}
			},
			function(System_Dir $dir) use ($dir_filter, $owner_user, $owner_group){
				if(!$dir_filter || $dir_filter($dir)){
					$dir->changeOwner($owner_user, $owner_group);
				}
			}
		);
	}

	/**
	 * chown() and chmod() files in directory
	 *
	 * @param null|int|bool $files_chmod [optional]
	 * @param null|string|int $owner_user [optional] if NULL, ET_FS_OWNER_USER is used. If empty string, chown() is skipped.
	 * @param null|string|int $owner_group [optional] if NULL, ET_FS_OWNER_GROUP is used. If empty string, chgrp() is skipped.
	 * @param callable $file_filter [optional] Callback like function(System_File $file) returning TRUE chown()/chmod() should be applied or FALSE if not
	 */
	public function setFilesPermissions($files_chmod = null, $owner_user = null, $owner_group = null, callable $file_filter = null){
		$this->passThrough(
			function(System_File $file) use ($file_filter, $files_chmod, $owner_user, $owner_group){
				if(!$file_filter || $file_filter($file)){
					$file->setPermissions($files_chmod, $owner_user, $owner_group);
				}
			}
		);
	}

	/**
	 * chown() and chmod() subdirectories in directory
	 *
	 * @param null|int|bool $dirs_chmod [optional]
	 * @param null|string|int $owner_user [optional] if NULL, ET_FS_OWNER_USER is used. If empty string, chown() is skipped.
	 * @param null|string|int $owner_group [optional] if NULL, ET_FS_OWNER_GROUP is used. If empty string, chgrp() is skipped.
	 * @param callable $dir_filter [optional] Callback like function(System_Dir $dir) returning TRUE chown()/chmod() should be applied or FALSE if not
	 */
	public function setSubdirectoriesPermissions($dirs_chmod = null, $owner_user = null, $owner_group = null, callable $dir_filter = null){
		$this->passThrough(
			null,
			function(System_Dir $dir) use ($dir_filter, $dirs_chmod, $owner_user, $owner_group){
				if(!$dir_filter || $dir_filter($dir)){
					$dir->setPermissions($dirs_chmod, $owner_user, $owner_group);
				}
			}
		);
	}

	/**
	 * chown() directory, subdirectories and files
	 *
	 * @param null|int|bool $dirs_chmod [optional]
	 * @param null|int|bool $files_chmod [optional]
	 * @param null|string|int $owner_user [optional] if NULL, ET_FS_OWNER_USER is used. If empty string, chown() is skipped.
	 * @param null|string|int $owner_group [optional] if NULL, ET_FS_OWNER_GROUP is used. If empty string, chgrp() is skipped.
	 * @param callable $dir_filter [optional] Callback like function(System_Dir $dir) returning TRUE chown()/chmod() should be applied or FALSE if not
	 * @param callable $file_filter [optional] Callback like function(System_File $file) returning TRUE chown()/chmod() should be applied or FALSE if not
	 */
	function setPermissionsRecursive($dirs_chmod = null, $files_chmod = null, $owner_user = null, $owner_group = null, callable $dir_filter = null, callable $file_filter = null){
		$this->setPermissions($dirs_chmod, $owner_user, $owner_group);
		$this->passThrough(
			function(System_File $file) use ($file_filter, $files_chmod, $owner_user, $owner_group){
				if(!$file_filter || $file_filter($file)){
					$file->setPermissions($files_chmod, $owner_user, $owner_group);
				}
			},
			function(System_Dir $dir) use ($dir_filter, $dirs_chmod, $owner_user, $owner_group){
				if(!$dir_filter || $dir_filter($dir)){
					$dir->setPermissions($dirs_chmod, $owner_user, $owner_group);
				}
			}
		);
	}

	/**
	 * @param callable $file_filter [optional] Callback like function(System_File $file) returning TRUE if file should remain in list or FALSE if not
	 *
	 * @return System_File[]
	 */
	function listFiles(callable $file_filter = null){
		$output = array();
		$this->passThrough(
			function(System_File $file) use ($file_filter, &$output){
				if(!$file_filter || $file_filter($file)){
					$output[$file->getName()] = $file;
				}
			}
		);
		ksort($output, SORT_NATURAL | SORT_ASC);
		return $output;
	}

	/**
	 * @param callable $file_filter [optional] Callback like function($file_name, $file_path) returning TRUE if file should remain in list or FALSE if not
	 * @return array
	 */
	function listFileNames(callable $file_filter = null){
		$dh = $this->open();
		$output = array();
		while(($file = $dh->read()) !== false){
			if($file == "." || $file == ".."){
				continue;
			}

			$fp = $this->getPath() . $file;
			if(!is_file($fp)){
				continue;
			}

			if($file_filter && !$file_filter($file, $fp)){
				continue;
			}

			$output[] = $file;
		}
		$dh->close();
		sort($output, SORT_NATURAL | SORT_ASC);
		return $output;
	}

	/**
	 * @param callable $dir_filter [optional] Callback like function(System_Dir $dir) returning TRUE if directory should remain in list or FALSE if not
	 *
	 * @return System_Dir[]
	 */
	function listDirs(callable $dir_filter = null){
		$output = array();
		$this->passThrough(
			null,
			function(System_Dir $dir) use ($dir_filter, &$output){
				if(!$dir_filter || $dir_filter($dir)){
					$output[$dir->getName()] = $dir;
				}
			}
		);
		ksort($output, SORT_NATURAL | SORT_ASC);
		return $output;
	}

	/**
	 * @param callable $dir_filter [optional] Callback like function($dir_name, $dir_path) returning TRUE if file should remain in list or FALSE if not
	 * @return array
	 */
	function listDirNames(callable $dir_filter = null){
		$dh = $this->open();
		$output = array();
		while(($dir_name = $dh->read()) !== false){
			if($dir_name == "." || $dir_name == ".."){
				continue;
			}

			$dp = $this->getPath() . $dir_name;
			if(!is_dir($dp)){
				continue;
			}

			if($dir_filter && !$dir_filter($dir_name, $dp)){
				continue;
			}

			$output[] = $dir_name;
		}
		$dh->close();
		sort($output, SORT_NATURAL | SORT_ASC);
		return $output;
	}

	/**
	 * @param bool $dirs_first [optional]
	 * @param callable $dir_filter [optional] Callback like function(System_Dir $dir) returning TRUE if directory should remain in list or FALSE if not
	 * @param callable $file_filter [optional] Callback like function(System_File $file) returning TRUE if file should remain in list or FALSE if not
	 *
	 * @return System_Dir[]|System_File[]
	 */
	function listMixed($dirs_first = true, callable $dir_filter = null, callable $file_filter = null){
		$dirs = $this->listDirs($dir_filter);
		$files = $this->listFiles($file_filter);

		if(!$dirs && !$files){
			return array();
		}

		if(!$dirs){
			return $files;
		}

		if(!$files){
			return $dirs;
		}

		$output = $dirs + $files;
		if($dirs_first){
			return $output;
		}

		ksort($output, SORT_NATURAL | SORT_ASC);
		return $output;
	}


	/**
	 * @param callable $dir_filter [optional]
	 * @param callable $file_filter [optional]
	 *
	 * @return System_Dir_Tree
	 */
	function listTree(callable $dir_filter = null, callable $file_filter = null){
		$this->checkExists();
		return new System_Dir_Tree($this, $dir_filter, $file_filter);
	}

	/**
	 * @return string
	 */
	function getRealPath(){
		return $this->normalizeDirPath(realpath($this->path));
	}

	/**
	 * @param System_Dir $target_parent_directory [optional]
	 * @param null|string $target_directory_name [optional]
	 * @param bool $remove_if_exists [optional]
	 * @param bool $overwrite_existing_files [optional]
	 *
	 * @throws System_Exception
	 * @return System_Dir
	 */
	function copyTo(System_Dir $target_parent_directory = null, $target_directory_name = null, $remove_if_exists = false, $overwrite_existing_files = true){

		$this->checkIsReadable();

		if(!$target_parent_directory){
			$target_parent_directory = $this->getParentDir();
		}

		if(!$target_parent_directory->exists()){
			$target_parent_directory->create();
		}

		if($target_directory_name === null){
			$target_directory_name = $this->getName();
		}

		$target_dir = new System_Dir($target_parent_directory->getPath() . $target_directory_name . "/");
		$target_dir->create($remove_if_exists);

		$files = $this->listFiles();
		foreach($files as $file_name => $file){
			$target_file = new System_File($target_dir->getPath() . $file_name);
			if($target_file->exists() && !$overwrite_existing_files){
				continue;
			}
			$file->copyTo($target_dir, $file_name, true);
		}

		$dirs = $this->listDirs();
		foreach($dirs as $dir_name => $dir){
			$dir->copyTo($target_dir, $dir_name, $remove_if_exists, $overwrite_existing_files);
		}

		return $target_dir;
	}

	/**
	 * @param System_Dir $target_parent_directory [optional]
	 * @param null|string $target_directory_name [optional]
	 * @param bool $remove_if_exists [optional]
	 * @param bool $overwrite_existing_files [optional]
	 *
	 * @throws System_Exception
	 * @return System_Dir
	 */
	function moveTo(System_Dir $target_parent_directory = null, $target_directory_name = null, $remove_if_exists = false, $overwrite_existing_files = true){

		$this->checkIsReadable();

		if(!$target_parent_directory){
			$target_parent_directory = $this->getParentDir();
		}

		if(!$target_parent_directory->exists()){
			$target_parent_directory->create();
		}

		if($target_directory_name === null){
			$target_directory_name = $this->getName();
		}

		$target_dir = new System_Dir($target_parent_directory->getPath() . $target_directory_name . "/");
		$target_dir->create($remove_if_exists);

		$files = $this->listFiles();
		foreach($files as $file_name => $file){
			$target_file = new System_File($target_dir->getPath() . $file_name);
			if($target_file->exists() && !$overwrite_existing_files){
				continue;
			}
			$file->moveTo($target_dir, $file_name, true);
		}

		$dirs = $this->listDirs();
		foreach($dirs as $dir_name => $dir){
			$dir->moveTo($target_dir, $dir_name, $remove_if_exists, $overwrite_existing_files);
		}

		$this->delete();
		return $target_dir;
	}
}