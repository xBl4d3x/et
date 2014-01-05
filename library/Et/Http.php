<?php
namespace Et;
class Http {

	/**
	 * @var \Et\Http_Request
	 */
	protected static $current_request;

	/**
	 * @param bool $override_super_global_variables
	 */
	public static function initializeRequest($override_super_global_variables = true){
		Http_Request::initialize($override_super_global_variables);
		static::$current_request = new Http_Request(ET_REQUEST_URL_WITH_QUERY, ET_REQUEST_METHOD);
	}

	/**
	 * @return \Et\Http_Request
	 */
	public static function getCurrentRequest() {
		if(!static::$current_request){
			static::$current_request = new Http_Request(ET_REQUEST_URL_WITH_QUERY, ET_REQUEST_METHOD);
		}
		return static::$current_request;
	}

	/**
	 * @param bool $including_query_string
	 * @return string
	 */
	public static function getCurrentURL($including_query_string = true){
		return static::getCurrentRequest()->getURL($including_query_string);
	}

	/**
	 * @param null|string $path [optional]
	 * @param null|mixed $default_value [optional]
	 * @return Http_Request_Data_GET|mixed
	 */
	public static function GET($path = null, $default_value = null){
		return Http_Request::GET($path, $default_value);
	}

	/**
	 * @param null|string $path [optional]
	 * @param null|mixed $default_value [optional]
	 * @return Http_Request_Data_POST|mixed
	 */
	public static function POST($path = null, $default_value = null){
		return Http_Request::POST($path, $default_value);
	}

	/**
	 * @param null|string $path [optional]
	 * @param null|mixed $default_value [optional]
	 * @return Http_Request_Data_SERVER|mixed
	 */
	public static function SERVER($path = null, $default_value = null){
		return Http_Request::SERVER($path, $default_value);
	}

	/**
	 * @param string $header
	 * @param null|string|int $value [optional]
	 * @param null|bool $replace [optional]
	 */
	public static function sendHeader($header, $value = null, $replace = null){
		Http_Headers::sendHeader($header, $value, $replace);
	}

	/**
	 * @param $headers
	 * @param null|bool $replace [optional]
	 *
	 * @throws Http_Headers_Exception
	 */
	public static function sendHeaders(array $headers, $replace = null){
		Http_Headers::sendHeaders($headers, $replace);
	}

	/**
	 * Redirection - 301 or 302
	 *
	 * @param string $target_URL
	 * @param bool $permanently [optional] Default: FALSE
	 * @param bool $die [optional] Default: TRUE
	 */
	public static function redirect($target_URL, $permanently = false, $die = true){
		Http_Headers::redirect($target_URL, $permanently, $die);
	}

	/**
	 * Send JSON response and exit
	 *
	 * @param mixed $json_data
	 * @param int $response_code [optional] Default: 200
	 */
	public static function responseJSON($json_data, $response_code = Http_Headers::CODE_200_OK){
		Http_Headers::responseJSON(
			$json_data,
			true,
			"utf-8",
			$response_code
		);
	}

	/**
	 * Send HTML response and exit
	 *
	 * @param string $html_content
	 * @param int $response_code [optional] Default: 200
	 */
	public static function responseHTML($html_content, $response_code = Http_Headers::CODE_200_OK){
		Http_Headers::responseHTML(
			$html_content,
			true,
			"utf-8",
			$response_code
		);
	}

	/**
	 * Send text response and exit
	 *
	 * @param string $text_content
	 * @param int $response_code [optional] Default: 200
	 */
	public static function responseText($text_content, $response_code = Http_Headers::CODE_200_OK){
		Http_Headers::responseText(
			$text_content,
			true,
			"utf-8",
			$response_code
		);
	}

	/**
	 * Send XML response
	 *
	 * @param string|\DOMDocument|\SimpleXMLElement $xml
	 * @param string $type [optional] Default: text/xml
	 * @param int $response_code [optional] Default: 200
	 */
	public static function responseXML($xml, $type = "text/xml", $response_code = Http_Headers::CODE_200_OK){
		Http_Headers::responseXML(
			$xml,
			true,
			"utf-8",
			$type,
			$response_code
		);
	}
}