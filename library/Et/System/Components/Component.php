<?php
namespace Et;
et_require("Object");
class System_Components_Component extends Object {

	/**
	 * Component name
	 *
	 * @var string
	 */
	protected $name;

	/**
	 * Human readable component label
	 *
	 * @var string
	 */
	protected $label = "";


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
	 * @param string $component_name
	 * @param string $label [optional]
	 * @param string $description [optional]
	 */
	function __construct($component_name, $label = "", $description = ""){
		$this->assert()->isVariableName($component_name);

		$this->name = $component_name;
		$this->setLabel($label);
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
				"Component '{$this->name}' is not installed, it can not be enabled",
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
				"Component '{$this->name}' is not installed, cannot change version number",
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
	public function getLabel() {
		return $this->label;
	}

	/**
	 * @param string $label
	 */
	public function setLabel($label) {
		$label = trim($label);
		if($this->label !== $label){
			$this->changed = true;
		}
		$this->label = $label;
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
	public function getName() {
		return $this->name;
	}

	/**
	 * @return string
	 */
	function __toString(){
		return $this->getName();
	}
}