<?php
namespace Et;

class Session_Config extends Config {

	const DEFAULT_SESSION_NAME = "ETSESSID";
	const SESSION_ENTROPY = 48;

	/**
	 * @var string
	 */
	protected static $_environment_config_section = "session";

	/**
	 * Definition of config properties
	 *
	 * @var array
	 */
	protected static $_definition = array(
		"session_hijacking_IP_ignore_list" => [
			self::DEF_DESCRIPTION => "List of IPs or masks (* = any numbers, ? = any number)"
		],
		"keep_access_history_steps" =>  [
			self::DEF_NAME => "Keep last N access information to session",
			self::DEF_MIN_VALUE => 0
		],
		"secure_cookie_only" => [
			self::DEF_NAME => "Cookie when HTTPs only"
		],
		"cookie_path" => [self::DEF_NAME => "Cookie path/URI"],
		"cookie_lifetime" => [
			self::DEF_NAME => "Cookie lifetime (0 = till browser close)",
			self::DEF_MIN_VALUE => 0
		],
		"use_long_ID" => [
			self::DEF_NAME => "Use long (40 bytes) ID"
		],


	);

	/**
	 * @var bool
	 */
	protected $session_hijacking_detection_enabled = true;

	/**
	 * @var array
	 */
	protected $session_hijacking_IP_ignore_list = array();

	/**
	 * @var int
	 */
	protected $keep_access_history_steps = 5;

	/**
	 * @var string
	 */
	protected $session_handler;

	/**
	 * @var string
	 */
	protected $session_save_path;

	/**
	 * @var string
	 */
	protected $session_name = self::DEFAULT_SESSION_NAME;

	/**
	 * @var bool
	 */
	protected $use_session_cookies = true;

	/**
	 * @var bool
	 */
	protected $secure_cookie_only = false;

	/**
	 * @var string
	 */
	protected $cookie_domain = "";

	/**
	 * @var string
	 */
	protected $cookie_path;

	/**
	 * @var int
	 */
	protected $cookie_lifetime = 0;

	/**
	 * @var bool
	 */
	protected $http_cookie_only = true;

	/**
	 * @var int
	 */
	protected $use_long_ID = true;

	/**
	 * @return array
	 */
	public static function getDefaultValues() {
		$values = parent::getDefaultValues();

		$values["session_handler"] = ini_get("session.save_handler");

		$save_path = ini_get("session.save_path");
		if(strtolower($values["session_handler"]) == "files"){
			$session_dir = System::getTemporaryDataDir("sessions");
			if(!$session_dir->exists()){
				$session_dir->create();
				$save_path = (string)$session_dir;

			} elseif($session_dir->isWritable()){

				$save_path = (string)$session_dir;
			}
		}
		$values["session_save_path"] = $save_path;
		$values["cookie_path"] = ET_BASE_URI;

		return $values;
	}

	/**
	 * @return array
	 */
	public function getSessionHijackingIPIgnoreList() {
		return $this->session_hijacking_IP_ignore_list;
	}

	/**
	 * @return boolean
	 */
	public function getSessionHijackingDetectionEnabled() {
		return $this->session_hijacking_detection_enabled;
	}


	/**
	 * @return string
	 */
	public function getSessionHandler() {
		return $this->session_handler;
	}

	/**
	 * @return string
	 */
	public function getSessionSavePath() {
		return $this->session_save_path;
	}

	/**
	 * @return string
	 */
	public function getCookieDomain() {
		return $this->cookie_domain;
	}

	/**
	 * @return boolean
	 */
	public function getHttpCookieOnly() {
		return $this->http_cookie_only;
	}

	/**
	 * @return boolean
	 */
	public function getSecureCookieOnly() {
		return $this->secure_cookie_only;
	}

	/**
	 * @return int
	 */
	public function getCookieLifetime() {
		return $this->cookie_lifetime;
	}

	/**
	 * @return string
	 */
	public function getCookiePath() {
		return $this->cookie_path;
	}

	/**
	 * @return boolean
	 */
	public function getUseSessionCookies() {
		return $this->use_session_cookies;
	}

	/**
	 * @return string
	 */
	public function getSessionName() {
		return $this->session_name;
	}

	/**
	 * @return int
	 */
	public function getUseLongID() {
		return $this->use_long_ID;
	}

	/**
	 * @return int
	 */
	public function getKeepAccessHistorySteps() {
		return $this->keep_access_history_steps;
	}

	/**
	 * @return array
	 */
	function toIniSettings(){
		return array(
			"session.save_handler" => $this->getSessionHandler(),
			"session.save_path" => $this->getSessionSavePath(),
			"session.name" => $this->getSessionName(),
			"session.use_cookies" => (int)$this->getUseSessionCookies(),
			"session.cookie_secure" => (int)$this->getSecureCookieOnly(),
			"session.cookie_lifetime" => $this->getCookieLifetime(),
			"session.cookie_path" => $this->getCookiePath(),
			"session.cookie_domain" => trim($this->getCookieDomain()),
			"session.cookie_httponly" => (int)$this->getHttpCookieOnly(),
			"session.entropy_length" => static::SESSION_ENTROPY,
			"session.hash_function" => (int)$this->getUseLongID()
		);
	}


}