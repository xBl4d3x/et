<?php
namespace Et;
abstract class MVC_Modules_Module_Installer extends Object {

	/**
	 * @var MVC_Modules_Module_Metadata
	 */
	protected $module_metadata;

	/**
	 * @param MVC_Modules_Module_Metadata $module_metadata
	 */
	function __construct(MVC_Modules_Module_Metadata $module_metadata){
		$this->module_metadata = $module_metadata;
	}

	/**
	 * @return MVC_Modules_Module_Metadata
	 */
	function getModuleMetadata(){
		return $this->module_metadata;
	}

	/**
	 * @return string
	 */
	function getModuleID(){
		return $this->module_metadata->getModuleID();
	}

	/**
	 * @return string
	 */
	function getModuleName(){
		return $this->module_metadata->getModuleTitle();
	}

	/**
	 * @throws MVC_Modules_Exception
	 */
	public function installModule(){
		if($this->module_metadata->isInstalled()){
			return;
		}

		try {

			$this->install();

		} catch(Exception $e){
			throw new MVC_Modules_Exception(
				"Module {$this->getModuleID()} install failed - {$e->getMessage()}",
				MVC_Modules_Exception::CODE_INSTALLER_FAILURE,
				null,
				$e
			);
		}

		$this->module_metadata->install($this->module_metadata->getVersion());
	}

	/**
	 * @throws MVC_Modules_Exception
	 */
	abstract protected function install();

	/**
	 * @throws MVC_Modules_Exception
	 */
	function uninstallModule(){
		if(!$this->module_metadata->isInstalled()){
			return;
		}

		try {

			$this->uninstall();

		} catch(Exception $e){
			throw new MVC_Modules_Exception(
				"Module {$this->getModuleID()} uninstall failed - {$e->getMessage()}",
				MVC_Modules_Exception::CODE_INSTALLER_FAILURE,
				null,
				$e
			);
		}

		$this->module_metadata->uninstall();
	}

	/**
	 * @throws MVC_Modules_Exception
	 */
	abstract protected function uninstall();

	/**
	 * @throws MVC_Modules_Exception
	 */
	function enableModule(){
		$this->checkIfInstalled();
		$this->module_metadata->enable();
	}

	/**
	 * @throws MVC_Modules_Exception
	 */
	protected function checkIfInstalled(){
		if(!$this->module_metadata->isInstalled()){
			throw new MVC_Modules_Exception(
				"Module {$this->getModuleID()} is not installed",
				MVC_Modules_Exception::CODE_INSTALLER_FAILURE
			);
		}
	}

	/**
	 * @throws MVC_Modules_Exception
	 */
	function disableModule(){
		$this->checkIfInstalled();
		$this->module_metadata->disable();
	}


	function updateModule(){
		$this->checkIfInstalled();
		$installed_version = $this->module_metadata->getInstalledVersion();
		$current_version = $this->module_metadata->getVersion();

		if($installed_version == $current_version){
			return;
		}

		try {

			$this->update($installed_version, $current_version);

		} catch(Exception $e){
			throw new MVC_Modules_Exception(
				"Module {$this->getModuleID()} update from version {$installed_version} to version {$current_version} failed - {$e->getMessage()}",
				MVC_Modules_Exception::CODE_INSTALLER_FAILURE,
				null,
				$e
			);
		}

		$this->module_metadata->setInstalledVersion($current_version);
	}

	/**
	 * @param int $from_version
	 * @param int $to_version
	 */
	abstract protected function update($from_version, $to_version);
}