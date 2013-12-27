<?php
namespace Et;
abstract class Application_Installer extends Object {

	/**
	 * @var Application_Metadata
	 */
	protected $application_metadata;

	/**
	 * @param Application_Metadata $application_metadata
	 */
	function __construct(Application_Metadata $application_metadata){
		$this->application_metadata = $application_metadata;
	}

	/**
	 * @return Application_Metadata
	 */
	function getApplicationMetadata(){
		return $this->application_metadata;
	}

	/**
	 * @return string
	 */
	function getApplicationID(){
		return $this->application_metadata->getApplicationID();
	}

	/**
	 * @return string
	 */
	function getApplicationName(){
		return $this->application_metadata->getApplicationName();
	}

	/**
	 * @throws Application_Exception
	 */
	public function installApplication(){
		if($this->application_metadata->isInstalled()){
			return;
		}

		try {

			$this->install();

		} catch(Exception $e){
			throw new Application_Exception(
				"Application {$this->getApplicationID()} install failed - {$e->getMessage()}",
				Application_Exception::CODE_INSTALLER_FAILURE,
				null,
				$e
			);
		}

		$this->application_metadata->install($this->application_metadata->getVersion());
	}

	/**
	 * @throws Application_Exception
	 */
	abstract protected function install();

	/**
	 * @throws Application_Exception
	 */
	function uninstallApplication(){
		if(!$this->application_metadata->isInstalled()){
			return;
		}

		try {

			$this->uninstall();

		} catch(Exception $e){
			throw new Application_Exception(
				"Application {$this->getApplicationID()} uninstall failed - {$e->getMessage()}",
				Application_Exception::CODE_INSTALLER_FAILURE,
				null,
				$e
			);
		}

		$this->application_metadata->uninstall();
	}

	/**
	 * @throws Application_Exception
	 */
	abstract protected function uninstall();

	/**
	 * @throws Application_Exception
	 */
	function enableApplication(){
		$this->checkIfInstalled();
		$this->application_metadata->enable();
	}

	/**
	 * @throws Application_Exception
	 */
	protected function checkIfInstalled(){
		if(!$this->application_metadata->isInstalled()){
			throw new Application_Exception(
				"Application {$this->getApplicationID()} is not installed",
				Application_Exception::CODE_INSTALLER_FAILURE
			);
		}
	}

	/**
	 * @throws Application_Exception
	 */
	function disableApplication(){
		$this->checkIfInstalled();
		$this->application_metadata->disable();
	}


	function updateApplication(){
		$this->checkIfInstalled();
		$installed_version = $this->application_metadata->getInstalledVersion();
		$current_version = $this->application_metadata->getVersion();

		if($installed_version == $current_version){
			return;
		}

		try {

			$this->update($installed_version, $current_version);

		} catch(Exception $e){
			throw new Application_Exception(
				"Application {$this->getApplicationID()} update from version {$installed_version} to version {$current_version} failed - {$e->getMessage()}",
				Application_Exception::CODE_INSTALLER_FAILURE,
				null,
				$e
			);
		}

		$this->application_metadata->setInstalledVersion($current_version);
	}

	/**
	 * @param int $from_version
	 * @param int $to_version
	 */
	abstract protected function update($from_version, $to_version);
}