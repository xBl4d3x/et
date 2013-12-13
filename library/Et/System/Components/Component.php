<?php
namespace Et;
et_require("Object");
class System_Components_Component extends Object {

	/**
	 * Component id
	 *
	 * @var string
	 */
	protected $ID;

	/**
	 * Human readable component name
	 *
	 * @var string
	 */
	protected $name = "";


	/**
	 * Human readable component description
	 *
	 * @var string
	 */
	protected $description = "";

	/**
	 * Installed component version
	 *
	 * @var int
	 */
	protected $installed_version = 0;

	/**
	 * Is component installed?
	 *
	 * @var bool
	 */
	protected $installed = false;

	/**
	 * Is component enabled?
	 *
	 * @var bool
	 */
	protected $enabled = false;

	/**
	 * @var bool
	 */
	protected $changed = false;

	/**
	 * @param string $component_ID
	 * @param string $name [optional]
	 * @param string $description [optional]
	 */
	function __construct($component_ID, $name = "", $description = ""){
		$this->assert()->isIdentifier($component_ID);

		$this->ID = $component_ID;
		$this->setName($name);
		$this->setDescription($description);
		$this->changed = true;
	}

	function __wakeup(){
		$this->changed = false;
	}

	/**
	 * @return bool
	 */
	function hasChanged(){
		return $this->changed;
	}

	/**
	 * Enable component
	 *
	 * @throws System_Components_Exception when component is not installed
	 */
	function enable(){
		if(!$this->isInstalled()){
			throw new System_Components_Exception(
				"Component '{$this->ID}' is not installed, it can not be enabled",
				System_Components_Exception::CODE_NOT_INSTALLED
			);
		}

		if(!$this->enabled){
			$this->changed = true;
		}

		$this->enabled = true;
	}

	/**
	 * Disable component
	 */
	function disable(){
		if($this->enabled){
			$this->changed = true;
		}

		$this->enabled = false;
	}

	/**
	 * Is component enabled?
	 *
	 * @return bool
	 */
	function isEnabled(){
		return $this->isInstalled() && $this->enabled;
	}

	/**
	 * Install component
	 *
	 * @param int $version_number
	 *
	 * @throws System_Components_Exception
	 */
	function install($version_number){
		if(!$this->installed){
			$this->changed = true;
		}
		$this->installed = true;
		$this->setInstalledVersion($version_number);
	}

	/**
	 * Uninstall component
	 */
	function uninstall(){
		if($this->installed){
			$this->changed = true;
		}
		$this->installed = false;
		$this->installed_version = 0;
		$this->enabled = false;
	}

	/**
	 * Is component installed?
	 *
	 * @return bool
	 */
	function isInstalled(){
		return $this->installed && $this->installed_version > 0;
	}


	/**
	 * Set installed component version
	 *
	 * @param int $version
	 *
	 * @throws Debug_Assert_Exception when version is not number greater than 0
	 * @throws System_Components_Exception when component is not installed
	 */
	function setInstalledVersion($version){
		if(!$this->installed){
			throw new System_Components_Exception(
				"Component '{$this->ID}' is not installed, cannot change version number",
				System_Components_Exception::CODE_NOT_INSTALLED
			);
		}

		self::assert()->isGreaterThan($version, 0, "Version must be number greater than 0");

		if($this->installed_version != (int)$version){
			$this->changed = true;
		}

		$this->installed_version = (int)$version;
	}

	/**
	 * @return int
	 */
	public function getInstalledVersion() {
		return $this->installed_version;
	}

	/**
	 * @return string
	 */
	public function getDescription() {
		return $this->description;
	}

	/**
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * @param string $label
	 */
	public function setName($label) {
		$label = trim($label);
		if($this->name !== $label){
			$this->changed = true;
		}
		$this->name = $label;
	}

	/**
	 * @param string $description
	 */
	public function setDescription($description) {
		$description = trim($description);
		if($this->description !== $description){
			$this->changed = true;
		}
		$this->description = $description;
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
	function __toString(){
		return $this->getID();
	}
}