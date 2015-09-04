<?php
/**
 * LanguagePack.php
 *
 * Copyright 2003-2013, Moxiecode Systems AB, All rights reserved.
 */

/**
 * This class parses XML language packs and returns them as a php array with groups and subgroups.
 *
 * @package MOXMAN_Util
 */
class MOXMAN_Util_LanguagePack {
	/** @ignore */
	private $groups;

	/**
	 * Constructs a new LanguagePack instance.
	 */
	public function __construct() {
		$this->groups = array();
	}

	/**
	 * Loads the specified XML file into the groups array.
	 *
	 * @param string $path Path to XML file to load.
	 */
	public function load($path) {
		$this->groups = array();
		$xml = simplexml_load_file($path);

		foreach ($xml->language->group as $group) {
			$this->groups["" . $group["target"]] = array();

			foreach ($group->item as $item) {
				$this->groups["" . $group["target"]]["" . $item["name"]] = "" . $item;
			}
		}
	}

	/**
	 * Returns the groups as an name/value array with subarrays with key/values.
	 *
	 * @return Array Name/value array with groups and subgroups.
	 */
	public function getGroups() {
		return $this->groups;
	}
}

?>