<?php
namespace Et;
class Http_AJAXResponse_Content extends Http_AJAXResponse_Abstract {

	/**
	 * @var string
	 */
	protected $content = "";

	/**
	 * @param string $content [optional]
	 */
	function __construct($content = ""){
		$this->setContent($content);
	}

	/**
	 * @param string $content [optional]
	 * @return static|\Et\Http_AJAXResponse_Content
	 */
	public static function getInstance($content = ""){
		return new static($content);
	}

	/**
	 * @param string $content
	 * @return static|\Et\Http_AJAXResponse_Content
	 */
	public function setContent($content) {
		$this->content = (string)$content;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getContent() {
		return $this->content;
	}


}