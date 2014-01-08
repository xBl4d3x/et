<?php
namespace Et;
class Session_Metadata extends Session_Namespace {
	
	function __construct(){
		parent::__construct(Session::METADATA_NAMESPACE);
		if(!isset($this->data["created_by_IP"], $this->data["created_when"])){
			$this->sessionCreated();	
		}
	}
	
	protected function sessionCreated(){
		$this->data["created_by_IP"] = ET_REQUEST_IP;
		$this->data["created_when"] = time();
		$this->data["created_at_URL"] = ET_REQUEST_URL_WITH_QUERY;
		$this->data["created_by_user_agent"] = ET_REQUEST_USER_AGENT;
		
		$this->data["access_count"] = 0;
		
		$this->data["access_list"] = array();
	}
	
	function sessionAccessed(){

		$this->data["access_count"] = $this->getAccessCount() + 1;
		$this->data["last_access_by_IP"] = ET_REQUEST_IP;
		$this->data["last_access_when"] = time();
		$this->data["last_access_at_URL"] = ET_REQUEST_URL_WITH_QUERY;
		$this->data["last_access_by_user_agent"] = ET_REQUEST_USER_AGENT;
		
		$max_steps = Session::getSessionConfig()->getKeepAccessHistorySteps();
		if(!$max_steps){
			return;
		}
		
		if(!isset($this->data["access_list"])){
			$this->data["access_list"] = array();
		}
		
		$items_count = count($this->data["access_list"]);
		$to_remove = $items_count - $max_steps - 1;
		for($i = 0; $i < $to_remove; $i++){
			array_shift($this->data["access_list"]);
		}
		
		$record = array(
			"IP" => ET_REQUEST_IP,
			"when" => time(),
			"URL" => ET_REQUEST_URL_WITH_QUERY,
			"request_method" => ET_REQUEST_METHOD,
			"user_agent" => ET_REQUEST_USER_AGENT
		);

		$this->data["access_list"][] = $record;
	}

	/**
	 * @return int
	 */
	function getAccessCount(){
		return max(0, $this->getInt("access_count"));
	}

	/**
	 * @return array
	 */
	function getAccessList(){
		return $this->get("access_list", array());
	}

	/**
	 * @return string
	 */
	function getCreatedByIP(){
		return $this->getString("created_by_IP", "UNKNOWN");
	}

	/**
	 * @return string
	 */
	function getCreatedWhen(){
		return $this->getInt("created_when");
	}

	/**
	 * @return string
	 */
	function getCreatedAtURL(){
		return $this->getString("created_at_URL");
	}

	/**
	 * @return string
	 */
	function getCreatedByUserAgent(){
		return $this->getString("created_by_user_agent");
	}

	/**
	 * @return string
	 */
	function getLastAccessByIP(){
		return $this->getString("last_access_by_IP", "UNKNOWN");
	}

	/**
	 * @return string
	 */
	function getLastAccessWhen(){
		return $this->getInt("last_access_when");
	}

	/**
	 * @return string
	 */
	function getLastAccessAtURL(){
		return $this->getString("last_access_at_URL");
	}

	/**
	 * @return string
	 */
	function getLastAccessByUserAgent(){
		return $this->getString("last_access_by_user_agent");
	}

	
}