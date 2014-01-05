<?php
namespace Et;
et_require("Debug_Error_Handler_Abstract");
class Debug_Error_Handler_Display extends Debug_Error_Handler_Abstract {

	/**
	 * @var bool
	 */
	protected $display_HTML = true;

	/**
	 * @var bool
	 */
	protected $scripts_displayed = false;

	/**
	 * @param boolean $display_HTML
	 */
	public function setDisplayHTML($display_HTML) {
		$this->display_HTML = (bool)$display_HTML;
	}

	/**
	 * @return boolean
	 */
	public function getDisplayHTML() {
		return $this->display_HTML;
	}


	/**
	 * @param Debug_Error $e
	 */
	protected function _handleError(Debug_Error $e){
		if($this->display_HTML){
			$this->displayHTML($e);
		} else {
			$this->displayText($e);
		}
		$e->setDisplayed(true);
	}

	protected function displayHTML(Debug_Error $e){
		if(!headers_sent()){
			header("Content-type: text/html;charset=utf-8");
		}

		if(!$this->scripts_displayed){
			$this->displayScripts();
		}


	}

	public function displayScripts(){
		?>
		<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
		<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jqueryui/1.10.2/jquery-ui.min.js"></script>
		<?php
	}

	public function displayHTMLHeader(Debug_Error $e){
		if($e->isException()){
			$output = "Exception {$e->getExceptionClass()} - {$e->getErrorCodeLabel()} (code {$e->getErrorCode()}) occurred:\n";
		} else {
			$output = $e->getErrorCodeLabel() . " occurred:\n";
		}

		$output .= trim($e->getErrorMessage()) . "\n\n";
		$output .= "File: {$e->getFile()}\n";
		$output .= "Line: {$e->getLine()}\n";
		if($e->getURL()){
			$output .= "URL: " . $e->getURL() . "\n";
		}
		$output .= "Time: " . date("Y-m-d H:i:s", $e->getTimestamp()) . "\n";
		$output .= "Strict mode: " . ($e->getStrictModeEnabled() ? "YES" : "NO") . "\n";
		$output .= "On shutdown: " . ($e->hasOccurredOnShutdown() ? "YES" : "NO");
	}

	/**
	 * @param Debug_Error $e
	 */
	protected function displayText(Debug_Error $e){
		if(PHP_SAPI !== "cli" && !headers_sent()){
			header("Content-type:text/plain;charset=utf-8");
		}

		echo "\n" . $this->getErrorAsText($e);
	}
}