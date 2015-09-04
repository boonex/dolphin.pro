<?php
/**
 * JsonDecoder.php
 *
 * Copyright 2003-2013, Moxiecode Systems AB, All rights reserved.
 */

/**
 * JSON decoder written in native PHP. This can be used as a fallback for
 * earlier PHP versions that didn't have native JSON support.
 *
 * @package MOXMAN_Util
 */
class MOXMAN_Util_JsonDecoder {
	/** @ignore */
	private static $data, $obj, $len, $pos, $value, $token, $location, $lastLocation, $needProp;

	/** @ignore */
	private static $parents, $cur;

	const TOKEN_BOOL = 1;
	const TOKEN_INT = 2;
	const TOKEN_STR = 3;
	const TOKEN_FLOAT = 4;
	const TOKEN_NULL = 5;
	const TOKEN_START_OBJ = 6;
	const TOKEN_END_OBJ = 7;
	const TOKEN_START_ARRAY = 8;
	const TOKEN_END_ARRAY = 9;
	const TOKEN_KEY = 10;
	const TOKEN_SKIP = 11;
	const LOC_IN_ARRAY = 30;
	const LOC_IN_OBJECT = 40;
	const LOC_IN_BETWEEN = 50;

	/**
	 * Decodes the specified JSON string into an native PHP type.
	 *
	 * @param string $data JSON string to parse.
	 * @return Mixed Native PHP type based on JSON data.
	 */
	public static function decode($data) {
		self::$data = $data;
		self::$len = strlen($data);
		self::$pos = -1;
		self::$location = self::LOC_IN_BETWEEN;
		self::$lastLocation = array();
		self::$needProp = false;

		$data = self::readValue();

		// Cleanup
		self::$lastLocation = null;
		self::$data = null;

		return $data;
	}

	/** @ignore */
	private static function readValue() {
		self::$obj = array();
		self::$parents = array();
		self::$cur =& self::$obj;
		$key = null;
		$loc = self::LOC_IN_ARRAY;

		while (self::readToken()) {
			switch (self::$token) {
				case self::TOKEN_STR:
				case self::TOKEN_INT:
				case self::TOKEN_BOOL:
				case self::TOKEN_FLOAT:
				case self::TOKEN_NULL:
					switch (self::$location) {
						case self::LOC_IN_OBJECT:
							self::$cur->{$key} = self::$value;
							break;

						case self::LOC_IN_ARRAY:
							self::$cur[] = self::$value;
							break;

						default:
							return self::$value;
					}
					break;

				case self::TOKEN_KEY:
					$key = self::$value;
					break;

				case self::TOKEN_START_OBJ:
					if ($loc == self::LOC_IN_OBJECT) {
						self::addArray($key, true);
					} else {
						self::addArray(null, true);
					}

					$cur =& $obj;
					$loc = self::$location;
					break;

				case self::TOKEN_START_ARRAY:
					if ($loc == self::LOC_IN_OBJECT) {
						self::addArray($key, false);
					} else {
						self::addArray(null, false);
					}

					$cur =& $obj;
					$loc = self::$location;
					break;

				case self::TOKEN_END_OBJ:
				case self::TOKEN_END_ARRAY:
					$loc = self::$location;

					if (count(self::$parents) > 0) {
						self::$cur =& self::$parents[count(self::$parents) - 1];
						array_pop(self::$parents);
					}
					break;
			}
		}

		return self::$obj[0];
	}

	// This method was needed since PHP is crapy and doesn't have pointers/references
	/** @ignore */
	private static function addArray($key, $newObj) {
		self::$parents[] =& self::$cur;
		$ar = $newObj ? new stdClass() : array();

		if ($key) {
			self::$cur->{$key} =& $ar;
		} else {
			self::$cur[] =& $ar;
		}

		self::$cur =& $ar;
	}

	/** @ignore */
	private static function readToken() {
		$chr = self::read();

		if ($chr != null) {
			switch ($chr) {
				case '[':
					self::$lastLocation[] = self::$location;
					self::$location = self::LOC_IN_ARRAY;
					self::$token = self::TOKEN_START_ARRAY;
					self::$value = null;
					self::readAway();
					return true;

				case ']':
					self::$location = array_pop(self::$lastLocation);
					self::$token = self::TOKEN_END_ARRAY;
					self::$value = null;
					self::readAway();

					if (self::$location == self::LOC_IN_OBJECT) {
						self::$needProp = true;
					}

					return true;

				case '{':
					self::$lastLocation[] = self::$location;
					self::$location = self::LOC_IN_OBJECT;
					self::$needProp = true;
					self::$token = self::TOKEN_START_OBJ;
					self::$value = null;
					self::readAway();
					return true;

				case '}':
					self::$location = array_pop(self::$lastLocation);
					self::$token = self::TOKEN_END_OBJ;
					self::$value = null;
					self::readAway();

					if (self::$location == self::LOC_IN_OBJECT) {
						self::$needProp = true;
					}

					return true;

				// String
				case '"':
				case '\'':
					return self::readString($chr);

				// Null
				case 'n':
					return self::readNull();

				// Bool
				case 't':
				case 'f':
					return self::readBool($chr);

				default:
					// Is number
					if (is_numeric($chr) || $chr == '-' || $chr == '.') {
						return self::readNumber($chr);
					}

					self::readAway();

					return true;
			}
		}

		return false;
	}

	/** @ignore */
	private static function readBool($chr) {
		self::$token = self::TOKEN_BOOL;
		self::$value = $chr == 't';

		if ($chr == 't') {
			self::skip(3); // rue
		} else {
			self::skip(4); // alse
		}

		self::readAway();

		if (self::$location == self::LOC_IN_OBJECT && !self::$needProp) {
			self::$needProp = true;
		}

		return true;
	}

	/** @ignore */
	private static function readNull() {
		self::$token = self::TOKEN_NULL;
		self::$value = null;

		self::skip(3); // ull
		self::readAway();

		if (self::$location == self::LOC_IN_OBJECT && !self::$needProp) {
			self::$needProp = true;
		}

		return true;
	}

	/** @ignore */
	private static function readString($quote) {
		$output = "";
		self::$token = self::TOKEN_STR;
		$endString = false;

		while (($chr = self::peek()) !== null) {
			switch ($chr) {
				case '\\':
					// Read away slash
					self::read();

					// Read escape code
					$chr = self::read();
					switch ($chr) {
						case 't':
							$output .= "\t";
							break;

						case 'b':
							$output .= chr(8);
							break;

						case 'f':
							$output .= chr(12);
							break;

						case 'r':
							$output .= "\r";
							break;

						case 'n':
							$output .= "\n";
							break;

						case 'u':
							$output .= self::int2utf8(hexdec(self::read(4)));
							break;

						default:
							$output .= $chr;
							break;
					}

					break;

				case '\'':
				case '"':
					if ($chr == $quote) {
						$endString = true;
					}

					$chr = self::read();
					if ($chr != -1 && $chr != $quote) {
						$output .= $chr;
					}

					break;

				default:
					$output .= self::read();
			}

			// String terminated
			if ($endString) {
				break;
			}
		}

		self::readAway();
		self::$value = $output;

		// Needed a property
		if (self::$needProp) {
			self::$token = self::TOKEN_KEY;
			self::$needProp = false;
			return true;
		}

		if (self::$location == self::LOC_IN_OBJECT && !self::$needProp) {
			self::$needProp = true;
		}

		return true;
	}

	// @codeCoverageIgnoreStart

	/** @ignore */
	private static function int2utf8($int) {
		$int = intval($int);

		switch ($int) {
			case 0:
				return chr(0);

			case ($int & 0x7F):
				return chr($int);

			case ($int & 0x7FF):
				return chr(0xC0 | (($int >> 6) & 0x1F)) . chr(0x80 | ($int & 0x3F));

			case ($int & 0xFFFF):
				return chr(0xE0 | (($int >> 12) & 0x0F)) . chr(0x80 | (($int >> 6) & 0x3F)) . chr (0x80 | ($int & 0x3F));

			case ($int & 0x1FFFFF):
				return chr(0xF0 | ($int >> 18)) . chr(0x80 | (($int >> 12) & 0x3F)) . chr(0x80 | (($int >> 6) & 0x3F)) . chr(0x80 | ($int & 0x3F));
		}
	}

	// @codeCoverageIgnoreEnd

	/** @ignore */
	private static function readNumber($start) {
		$value = "";
		$isFloat = false;

		self::$token = self::TOKEN_INT;
		$value .= $start;

		while (($chr = self::peek()) !== null) {
			// Ignore whitespace
			if ($chr === " " || $chr === "\t" || $chr === "\r" || $chr === "\n") {
				self::read();
				continue;
			}

			if (is_numeric($chr) || $chr === '-' || $chr === '.' || $chr === "e" || $chr === "E" || $chr === "+") {
				if ($chr == '.') {
					$isFloat = true;
				}

				$value .= self::read();
			} else {
				break;
			}
		}

		self::readAway();

		if ($isFloat) {
			self::$token = self::TOKEN_FLOAT;
			self::$value = floatval($value);
		} else {
			self::$value = intval($value);
		}

		if (self::$location == self::LOC_IN_OBJECT && !self::$needProp) {
			self::$needProp = true;
		}

		return true;
	}

	/** @ignore */
	private static function readAway() {
		while (($chr = self::peek()) !== null) {
			if ($chr != ':' && $chr != ',' && $chr != ' ') {
				return;
			}

			self::read();
		}
	}

	/** @ignore */
	private static function read($len = 1) {
		if (self::$pos < self::$len - 1) {
			if ($len > 1) {
				$str = substr(self::$data, self::$pos + 1, $len);
				self::$pos += $len;

				return $str;
			} else {
				return self::$data[++self::$pos];
			}
		}

		return null;
	}

	/** @ignore */
	private static function skip($len) {
		self::$pos += $len;
	}

	/** @ignore */
	private static function peek() {
		if (self::$pos < self::$len - 1) {
			return self::$data[self::$pos + 1];
		}

		return null;
	}
}

?>