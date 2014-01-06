<?php
namespace Et;

class MVC_Layout_Content extends Object {



	/**
	 * @var string
	 */
	protected $ID;

	/**
	 * @var bool
	 */
	protected $is_static = true;

	/**
	 * @var string
	 */
	protected $content = "";

	/**
	 * @var string
	 */
	protected $position_name;

	/**
	 * @var string
	 */
	protected $position_order;

	/**
	 * @var bool
	 */
	protected $position_required = true;

	/**
	 * @param string $ID
	 * @param string $content
	 * @param $position_name
	 */
	function __construct($ID, $content, $position_name){
		$this->ID = (string)$ID;
		$this->content = (string)$content;
		$this->position_name = (string)$position_name;
	}

	/**
	 * @return string
	 */
	public function getID() {
		return $this->ID;
	}

	/**
	 * @return string
	 */
	public function getContent() {
		return $this->content;
	}

	/**
	 * @return boolean
	 */
	public function getIsStatic() {
		return $this->is_static;
	}

	/**
	 * @return string
	 */
	public function getPositionName() {
		return $this->position_name;
	}

	/**
	 * @return string
	 */
	public function getPositionOrder() {
		return $this->position_order;
	}

	/**
	 * @return boolean
	 */
	public function getPositionRequired() {
		return $this->position_required;
	}

	/**
	 * @param string|int $position_order
	 */
	public function setPositionOrder($position_order) {
		if($position_order == MVC_Layout::CONTENT_ORDER_FIRST || $position_order == MVC_Layout::CONTENT_ORDER_LAST){
			$this->position_order = $position_order;
		} else {
			$this->position_order = (int)$position_order;
		}
	}

	/**
	 * @param boolean $position_required
	 */
	public function setPositionRequired($position_required) {
		$this->position_required = (bool)$position_required;
	}

	/**
	 * @param boolean $is_static
	 */
	public function setIsStatic($is_static) {
		$this->is_static = (bool)$is_static;
	}

	/**
	 * @return string
	 */
	function __toString(){
		return $this->getContent();
	}

}