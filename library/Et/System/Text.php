<?php
namespace Et;
et_require("Object");

class System_Text extends Object {

	const PAD_LEFT = STR_PAD_LEFT;
	const PAD_RIGHT = STR_PAD_RIGHT;
	const PAD_BOTH = STR_PAD_BOTH;

	/**
	 * @var System_Text_Transliteration_Abstract
	 */
	protected static $transliteration;

	/**
	 * @var string
	 */
	protected $text = "";

	/**
	 * @var string
	 */
	protected $charset = "UTF-8";

	/**
	 * @param string $text [optional]
	 * @param null|string $charset [optional] NULL = default charset (UTF-8)
	 * @param bool $check_encoding [optional]
	 */
	function __construct($text = "", $charset = null, $check_encoding = true){
		$this->setText($text, $charset, $check_encoding);
	}

	/**
	 * @param string $text [optional]
	 * @param null|string $charset [optional] NULL = default charset (UTF-8)
	 * @param bool $check_encoding [optional]
	 *
	 * @return static
	 */
	public static function getInstance($text = "", $charset = null, $check_encoding = true){
		return new static($text, $charset, $check_encoding);
	}

	/**
	 * @param string $text
	 * @param null|string $charset [optional] NULL = current charset
	 * @param bool $check_encoding [optional]
	 *
	 * @throws System_Text_Exception
	 */
	function setText($text, $charset = null, $check_encoding = true){
		if($charset !== null){
			$this->charset = (string)$charset;
		}
		$this->text = (string)$text;

		if($check_encoding && !mb_check_encoding($this->text, $this->charset)){
			throw new System_Text_Exception(
				"Input text doesn't seem to be encoded in {$this->charset}",
				System_Text_Exception::CODE_INVALID_ENCODING
			);
		}
	}

	/**
	 * @return string
	 */
	function getText(){
		return $this->text;
	}

	/**
	 * @return int
	 */
	function getLength(){
		return mb_strlen($this->text, $this->charset);
	}

	/**
	 * @return int
	 */
	function getSizeInBytes(){
		return strlen($this->text);
	}

	/**
	 * @param string $target_charset
	 *
	 * @return System_Text
	 */
	function convertCharset($target_charset){
		$target_charset = (string)$target_charset;
		return new static(
			mb_convert_encoding($this->charset, $target_charset, $this->charset),
			$target_charset
		);
	}



	/**
	 * @param int $pad_length
	 * @param string $pad_string [optional] Default: ' ' (space)
	 * @param int $pad_type [optional] One of Et\System_Text::PAD_*. Default: Et\System_Text::PAD_RIGHT
	 *
	 * @return string the padded string.
	 */
	public function pad($pad_length, $pad_string = " ", $pad_type = self::PAD_RIGHT){
		$diff = strlen($this->text) - $this->getLength();
		return str_pad($this->text, $pad_length + $diff, $pad_string, $pad_type);
	}

	/**
	 * @return string
	 */
	public function toUpperCase(){
		return mb_strtolower($this->text, $this->charset);
	}

	/**
	 * @return string
	 */
	public function toLowerCase(){
		return mb_strtolower($this->text, $this->charset);
	}

	/**
	 * @return string
	 */
	public function getFirstCharacter(){
		return $this->getFirstCharacters(1);
	}

	/**
	 * @param int $characters_count
	 * @return string
	 */
	public function getFirstCharacters($characters_count){
		return $this->getSubstring(0, max(1, (int)$characters_count));
	}

	/**
	 * @return string
	 */
	public function getLastCharacter(){
		return $this->getLastCharacters(1);
	}

	/**
	 * @param int $characters_count
	 * @return string
	 */
	public function getLastCharacters($characters_count){
		$characters_count = max(1, (int)$characters_count);
		return $this->getSubstring(-$characters_count, $characters_count);
	}

	/**
	 * @param bool $lower_case_rest [optional]
	 *
	 * @return string
	 */
	public function upperCaseFirst($lower_case_rest = false){
		if($this->text === ""){
			return $this->text;
		}

		$text = $this->getSubstring(1);
		if($lower_case_rest){
			$text = mb_strtolower($text, $this->charset);
		}
		$first_char = $this->getFirstCharacter();
		return mb_strtoupper($first_char, $this->charset) . $text;
	}

	/**
	 * @param bool $upper_case_rest [optional]
	 *
	 * @return string
	 */
	public function lowerCaseFirst($upper_case_rest = false){
		if($this->text === ""){
			return $this->text;
		}

		$text = $this->getSubstring(1);
		if($upper_case_rest){
			$text = mb_strtoupper($text, $this->charset);
		}
		$first_char = $this->getFirstCharacter();
		return mb_strtolower($first_char, $this->charset) . $text;
	}


	/**
	 * @param bool $lower_case_rest [optional]
	 *
	 * @return string
	 */
	public function upperCaseWords($lower_case_rest = false){

		$text = $this->text;
		if($lower_case_rest){
			$text = mb_strtolower($text, $this->charset);
		}

		return mb_convert_case($text, MB_CASE_TITLE);
	}

	/**
	 * @param string $search_for
	 * @param bool $case_sensitive [optional]
	 * @param null|int $offset [optional]
	 *
	 * @return int
	 */
	public function getSubstringPosition($search_for, $case_sensitive = true, $offset = null){
		if($case_sensitive){
			return mb_strpos($this->text, $search_for, $offset, $this->charset);
		} else {
			return mb_stripos($this->text, $search_for, $offset, $this->charset);
		}
	}

	/**
	 * @param string $search_for
	 * @param bool $case_sensitive [optional]
	 * @param null|int $offset [optional]
	 *
	 * @return int
	 */
	public function getLastSubstringPosition($search_for, $case_sensitive = true, $offset = null){
		if($case_sensitive){
			return mb_strrpos($this->text, $search_for, $offset, $this->charset);
		} else {
			return mb_strripos($this->text, $search_for, $offset, $this->charset);
		}
	}

	/**
	 * @param bool $skip_empty_lines [optional]
	 * @param int $offset [optional] From which line index to start (before $skip_empty_lines and $line_formatter is applied)
	 * @param callable $line_formatter [optional] Callback like function(&$line, $line_idx) , if returns FALSE, line is not included in result
	 * @param null $max_result_lines_count [optional] How many lines to return maximally
	 * @return array
	 */
	function getLines($skip_empty_lines = false, $offset = 0,  callable $line_formatter = null, $max_result_lines_count = null){

		if($this->text === ""){
			return array();
		}

		$offset = max(0, (int)$offset);

		if(strpos($this->text, "\r\n") !== false){
			$lines = explode("\r\n", $this->text);
		} else {
			$lines = explode("\n", $this->text);
		}

		if($offset > 0){
			while($offset > 0 && $lines){
				array_shift($lines);
				--$offset;
			}

			if(!$lines){
				return array();
			}
		}

		if(!$skip_empty_lines && !$line_formatter){
			return $lines;
		}

		$output = array();
		$lines_count = 0;
		foreach($lines as $i => $line){

			if($skip_empty_lines && trim($line) === ""){
				continue;
			}

			if($line_formatter && $line_formatter($line, $i) === false){
				continue;
			}

			$output[] = $line;
			$lines_count++;

			if($max_result_lines_count !== null && $lines_count == $max_result_lines_count){
				break;
			}
		}

		return $output;
	}

	/**
	 * @return int
	 */
	public function getLinesCount(){
		if($this->text === ""){
			return 0;
		}
		return substr_count($this->text, "\n") + 1;
	}

	/**
	 * @param int $start
	 * @param null|int $length [optional]
	 *
	 * @return string
	 */
	public function getSubstring($start, $length = null){
		if($length === null){
			$length = $this->getLength();
		}
		return mb_substr($this->text, $start, $length, $this->charset);
	}

	/**
	 * Replace constants wrapped in '%' in text
	 *
	 * <example>
	 * $text = "PHP version is {PHP_VERSION}";
	 * echo System_Text::replaceConstants($text); // outputs: PHP version is 5.4.7
	 * </example>
	 *
	 * @param array $custom_constants [optional]
	 * @param string $constant_prefix [optional]
	 * @param string $constant_postfix [optional]
	 *
	 * @return string
	 */
	public function replaceConstants(array $custom_constants = array(), $constant_prefix = '{', $constant_postfix = '}'){

		$constant_prefix = (string)$constant_prefix;
		$constant_postfix = (string)$constant_postfix;

		if($constant_prefix !== "" && strpos($this->text, $constant_prefix) === false){
			return $this->text;
		}

		if($constant_postfix !== "" && strpos($this->text, $constant_postfix) === false){
			return $this->text;
		}

		if(!preg_match_all(
				"~" .
				preg_quote($constant_prefix) .
				'(\w+|\w+(:?\\\w+)*::\w+)' .
				preg_quote($constant_prefix) .
				"~s",
				$this->text,
				$matches,
				PREG_SET_ORDER
			)
		   ||
		   !$matches
		){
			return $this->text;
		}

		$replacements = array();
		foreach($matches as $match){
			list(, $const) = $match;
			if(isset($custom_constants[$const])){
				$replacements["{$constant_prefix}{$const}{$constant_postfix}"] = $custom_constants[$const];
				continue;
			}
			if(defined($const)){
				$replacements["{$constant_prefix}{$const}{$constant_postfix}"] = constant($const);
			}
		}

		if(!$replacements){
			return $this->text;
		}

		return str_replace(
					array_keys($replacements),
					array_values($replacements),
					$this->text
		);
	}

	/**
	 * @param array $data
	 * @param string $key_prefix [optional]
	 * @param string $key_postfix [optional]
	 * @return string
	 */
	public function replaceData(array $data, $key_prefix = '{', $key_postfix = '}'){
		if(!$data){
			return $this->text;
		}

		$key_prefix = (string)$key_prefix;
		$key_postfix = (string)$key_postfix;

		$keys = array();
		$values = array();
		foreach($data as $k => $v){
			$keys[] = "{$key_prefix}{$k}{$key_postfix}";
			$values[] = (string)$v;
		}

		return str_replace($keys, $values, $this->text);
	}

	/**
	 * @return string
	 */
	public function removeAccents(){
		return $this->getTransliteration()->transliterate($this->text);
	}

	/**
	 * @param array $tag_to_remove [optional]
	 * @param array $tags_not_to_remove [optional]
	 * @return string
	 */
	public function removeHtmlTags(array $tag_to_remove = array(), array $tags_not_to_remove = array()){

		if(!$tag_to_remove && !$tags_not_to_remove){
			// pair tags
			return strip_tags(preg_replace('~<(\w+)\b.*?>.*?</\1>~si', '', $this->text));
		}

		if($tag_to_remove){
			return preg_replace('~<('. implode('|', $tag_to_remove) .')\b.*?(?:/>|>.*?</\1>)~si', '', $this->text);
		} else {
			return preg_replace('~<(?!(?:'. implode('|', $tags_not_to_remove) .')\b)(\w+)\b.*?(?:/>|>.*?</\1>)~si', '', $this->text);
		}
	}

	/**
	 * @return int
	 */
	public function getWordsCount(){
		return str_word_count($this->removeAccents(), 0);
	}

	/**
	 * @param System_Text_Transliteration_Abstract $transliteration
	 */
	public static function setTransliteration(System_Text_Transliteration_Abstract $transliteration) {
		static::$transliteration = $transliteration;
	}

	/**
	 * @return System_Text_Transliteration_Abstract
	 */
	public static function getTransliteration() {
		if(!static::$transliteration){
			et_require('System_Text_Transliteration_Default');
			static::$transliteration = new System_Text_Transliteration_Default();
		}
		return static::$transliteration;
	}

	/**
	 * @param array $lines
	 */
	public static function drawTextTable(array $lines){
		$columns_widths = array();
		foreach($lines as $line){
			foreach($line as $c => $column){
				if(!isset($columns_widths[$c])){
					$columns_widths[$c] = 0;
				}
				$col_lines = explode("\n", $column);
				foreach($col_lines as $line){
					$len = static::getInstance($line)->getLength() + 2;
					if($columns_widths[$c] < $len){
						$columns_widths[$c] = $len;
					}
				}
			}
		}

		$columns_count = count($columns_widths);
		$line_width = array_sum($columns_widths) + $columns_count + 1;
		echo str_repeat("=", $line_width) . "\n";
		foreach($lines as $l => &$line){
			foreach($line as $c => &$column){
				$w = $columns_widths[$c];
				if(!$c){
					echo "|";
				}
				echo static::getInstance(" {$column}")->pad($w, " ", self::PAD_RIGHT);
				echo "|";
			}
			echo "\n";
			if(!$l){
				echo str_repeat("-", $line_width) . "\n";
			}
		}
		echo str_repeat("=", $line_width) . "\n";
	}


	/**
	 * @return string
	 */
	function __toString(){
		return $this->text;
	}



}