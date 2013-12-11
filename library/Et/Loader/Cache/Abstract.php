<?php
namespace Et;
abstract class Loader_Cache_Abstract {

	/**
	 * @return array
	 */
	abstract function loadsPaths();

	/**
	 * @param array $paths
	 * @return bool
	 */
	abstract function storePaths(array $paths);

	/**
	 * @return bool
	 */
	abstract function clearPaths();

}