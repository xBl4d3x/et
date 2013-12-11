<?php
namespace Et;
et_require('Debug_Error_Handler_Abstract');
class Debug_Error_Handler_Logger extends Debug_Error_Handler_Abstract {

	/**
	 * @var string
	 */
	protected $log_file;
	
	function __construct(){
		parent::__construct();
		$this->log_file = ET_LOGS_PATH . @date("Y-m-d") . ".log";
	}

	/**
	 * @param Debug_Error $e
	 */
	protected function _handleError(Debug_Error $e) {
		$exists = @file_exists($this->log_file);
		if(@file_put_contents($this->log_file, $this->formatErrorToText($e), FILE_APPEND)){
			$e->setLogged(true);
			if(!$exists){
				@chown($this->log_file, ET_DEFAULT_FILES_CHMOD);
				if(ET_DEFAULT_CHOWN_GROUP){
					@chgrp($this->log_file, ET_DEFAULT_CHOWN_GROUP);
				}
				if(ET_DEFAULT_CHOWN_USER){
					@chown($this->log_file, ET_DEFAULT_CHOWN_USER);
				}
			}
		}
	}


}