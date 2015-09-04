<?php
/**
 * Json.php
 *
 * Copyright 2003-2013, Moxiecode Systems AB, All rights reserved.
 */

/**
 * Json encoder/decoder utility class. This will use the internal json functions in PHP
 * if they are available or fall back to custom PHP implementations.
 *
 * @package MOXMAN_Util
 */
class MOXMAN_Util_Json {
	/**
	 * Decodes the specified JSON string into an native PHP type.
	 *
	 * @codeCoverageIgnore
	 * @param string $str JSON string to parse.
	 * @return Mixed Native PHP type based on JSON data.
	 */
	public static function decode($str) {
		if (function_exists('json_decode')) {
			return json_decode($str);
		}

		// Fall back to custom JsonDecoder logic
		return MOXMAN_Util_JsonDecoder::decode($str);
	}

	/**
	 * Encodes the specified PHP type into a JSON string.
	 *
	 * @codeCoverageIgnore
	 * @param Object $obj PHP object to encode as JSON.
	 * @param Boolean $pretty Pretty output.
	 * @return A JSON string out of the specified object.
	 */
	public static function encode($obj, $pretty = false) {
		if (function_exists('json_encode')) {
			if ($pretty) {
				$opts = 0;
				if (defined('JSON_PRETTY_PRINT')) {
					$opts = JSON_PRETTY_PRINT;
				}

				// TODO: Remove this fix when we drop PHP 5.2 support
				if (version_compare(PHP_VERSION, '5.3.0', '<')) {
					return json_encode($obj);
				}

				return json_encode($obj, $opts);
			}

			return json_encode($obj);
		}

		// Fall back to custom JsonEncoder logic
		return MOXMAN_Util_JsonEncoder::encode($obj);
	}
}

?>
