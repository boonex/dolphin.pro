<?php
/**
 * IniParser.php
 *
 * Copyright 2003-2013, Moxiecode Systems AB, All rights reserved.
 */

/**
 * This class is used to parse ini files either from a string or a file.
 *
 * @package MOXMAN_Util
 */
class MOXMAN_Util_IniParser {
	/** @ignore */
	private $items;

	/**
	 * Loads and parses the specified file by path.
	 *
	 * @param string $path File path to ini file to parse.
	 */
	public function load($path) {
		return $this->parse(file_get_contents($path));
	}

	/**
	 * Parses the specified ini file string.
	 *
	 * @param string $str String to parse.
	 */
	public function parse($str) {
		$this->items = array();
		$currentSection = "";
		$lines = explode("\n", $str);

		foreach ($lines as $line) {
			$line = trim($line);

			if (strpos($line, "#") === 0) {
				continue;
			}

			if (strpos($line, "[") === 0 && strrpos($line, "]") == strlen($line) - 1) {
				$currentSection = substr($line, 1, strlen($line) - 2);
				continue;
			}

			$pos = strpos($line, '=');
			if ($pos !== false) {
				$key = trim(substr($line, 0, $pos));
				$value = trim(substr($line, $pos + 1));

				// Handle "value", 'value' and value
				if (strpos($value, "'") === 0 && strrpos($value, "'") == strlen($value) - 1) {
					$value = substr($value, 1, strlen($value) - 2);
				} else if (strpos($value, '"') === 0 && strrpos($value, '"') == strlen($value) - 1) {
					$value = substr($value, 1, strlen($value) - 2);
				} else {
					if ($value === "true") {
						$value = true;
					} else if ($value === "false") {
						$value = false;
					}
				}

				if (!$currentSection) {
					$this->items[$key] = $value;
				} else {
					if (!isset($this->items[$currentSection])) {
						$this->items[$currentSection] = array();
					}

					$this->items[$currentSection][$key] = $value;
				}
			}
		}

		return $this->items;
	}

	/**
	 * Returns the ini items where some item values will be arrays if they where declared in sections.
	 *
	 * @return Array Name/value array of config items.
	 */
	public function getItems() {
		return $this->items;
	}
}

?>
