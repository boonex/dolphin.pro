<?php
/**
 * JsonEncoder.php
 *
 * Copyright 2003-2013, Moxiecode Systems AB, All rights reserved.
 */

/**
 * JSON encoder written in native PHP. This can be used as a fallback for
 * earlier PHP versions that didn't have native JSON support.
 *
 * @package MOXMAN_Util
 */
class MOXMAN_Util_JsonEncoder {
	/**
	 * Encodes the specified PHP type into a JSON string.
	 *
	 * @param Object $input PHP object to encode as JSON.
	 * @return A JSON string out of the specified object.
	 */
	public static function encode($input) {
		switch (gettype($input)) {
			case 'boolean':
				return $input ? 'true' : 'false';

			case 'integer':
				return (int) $input;

			case 'float':
			case 'double':
				return (float) $input;

			case 'NULL':
				return 'null';

			case 'string':
				return self::encodeString($input);

			case 'array':
				return self::encodeArray($input);

			case 'object':
				return self::encodeArray(get_object_vars($input));
		}

		return '';
	}

	/** @ignore */
	private static function encodeString($input) {
		// Needs to be escaped
		if (preg_match('/[^a-zA-Z0-9]/', $input)) {
			$output = '';

			for ($i = 0, $l = strlen($input); $i < $l; $i++) {
				switch ($input[$i]) {
					case chr(8):
						$output .= "\\b";
						break;

					case "\t":
						$output .= "\\t";
						break;

					case "\f":
						$output .= "\\f";
						break;

					case "\r":
						$output .= "\\r";
						break;

					case "\n":
						$output .= "\\n";
						break;

					case '/':
						$output .= "\\/";
						break;

					case '\\':
						$output .= "\\\\";
						break;

					case '"':
						$output .= '\"';
						break;

					default:
						$byte = ord($input[$i]);

						if ($byte < 33) {
							$output .= sprintf('\u%04s', dechex($byte));
							break;
						}

						// @codeCoverageIgnoreStart
						if (($byte & 0xE0) == 0xC0) {
							$char = pack('C*', $byte, ord($input[$i + 1]));
							$i++;
							$output .= sprintf('\u%04s', bin2hex(self::utf82utf16($char)));
						} if (($byte & 0xF0) == 0xE0) {
							$char = pack('C*', $byte, ord($input[$i + 1]), ord($input[$i + 2]));
							$i += 2;
							$output .= sprintf('\u%04s', bin2hex(self::utf82utf16($char)));
						} if (($byte & 0xF8) == 0xF0) {
							$char = pack('C*', $byte, ord($input[$i + 1]), ord($input[$i + 2]), ord($input[$i + 3]));
							$i += 3;
							$output .= sprintf('\u%04s', bin2hex(self::utf82utf16($char)));
						} if (($byte & 0xFC) == 0xF8) {
							$char = pack('C*', $byte, ord($input[$i + 1]), ord($input[$i + 2]), ord($input[$i + 3]), ord($input[$i + 4]));
							$i += 4;
							$output .= sprintf('\u%04s', bin2hex(self::utf82utf16($char)));
						} if (($byte & 0xFE) == 0xFC) {
							$char = pack('C*', $byte, ord($input[$i + 1]), ord($input[$i + 2]), ord($input[$i + 3]), ord($input[$i + 4]), ord($input[$i + 5]));
							$i += 5;
							$output .= sprintf('\u%04s', bin2hex(self::utf82utf16($char)));
						} else if ($byte < 128) {
							$output .= $input[$i];
						}
						// @codeCoverageIgnoreEnd
				}
			}

			return '"' . $output . '"';
		}

		return '"' . $input . '"';
	}

	// @codeCoverageIgnoreStart

	/** @ignore */
	private static function utf82utf16($utf8) {
		if (function_exists('mb_convert_encoding')) {
			return mb_convert_encoding($utf8, 'UTF-16', 'UTF-8');
		}

		switch (strlen($utf8)) {
			case 1:
				return $utf8;

			case 2:
				return chr(0x07 & (ord($utf8[0]) >> 2)) . chr((0xC0 & (ord($utf8[0]) << 6)) | (0x3F & ord($utf8[1])));

			case 3:
				return chr((0xF0 & (ord($utf8[0]) << 4)) | (0x0F & (ord($utf8[1]) >> 2))) . chr((0xC0 & (ord($utf8[1]) << 6)) | (0x7F & ord($utf8[2])));
		}

		return '';
	}

	// @codeCoverageIgnoreEnd

	/** @ignore */
	private static function encodeArray($input) {
		$output = '';
		$isIndexed = true;

		$keys = array_keys($input);
		for ($i = 0, $l = count($keys); $i < $l; $i++) {
			if (!is_int($keys[$i])) {
				$output .= self::encodeString($keys[$i]) . ':' . self::encode($input[$keys[$i]]);
				$isIndexed = false;
			} else {
				$output .= self::encode($input[$keys[$i]]);
			}

			if ($i != count($keys) - 1) {
				$output .= ',';
			}
		}

		return $isIndexed ? '[' . $output . ']' : '{' . $output . '}';
	}
}

?>
