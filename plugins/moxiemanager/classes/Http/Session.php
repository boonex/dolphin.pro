<?php
/**
 * Session.php
 *
 * Copyright 2003-2013, Moxiecode Systems AB, All rights reserved.
 */

/**
 * This class wrapps in the HTTP Session and adds various useful options.
 *
 * @package MOXMAN_Http
 */
class MOXMAN_Http_Session {
	/**
	 * Constructs a new HTTP Session instance.
	 */
	public function __construct() {
	}

	/**
	 * Puts a session value by name into collection.
	 *
	 * @param string $name Session key.
	 * @param Mixed $value Session value.
	 * @return MOXMAN_Http_Session Session instance.
	 */
	public function put($name, $value) {
		$_SESSION[$name] = $value;

		return $this;
	}

	/**
	 * Returns the specified item from session.
	 *
	 * @param string $name Name of the item to retrive.
	 * @param string $default Default value to return if the specified item wasn't found.
	 * @return String Value of the specified string or an empty string if it wasn't found.
	 */
	public function get($name, $default = "") {
		if (isset($_SESSION[$name])) {
			return $_SESSION[$name];
		}

		return $default;
	}

	/**
	 * Returns true/false if the request has the specified item name or not.
	 *
	 * @param string $name Name if the item to check for.
	 * @return Boolean True/false if the item exists or not.
	 */
	public function has($name) {
		return isset($_SESSION[$name]);
	}

	/**
	 * Returns a name/value array with all session tiems.
	 *
	 * @return Array Name/value array with session items.
	 */
	public function getAll() {
		$all = array();
		foreach ($_SESSION as $key => $value) {
			$all[$key] = $value;
		}

		return $all;
	}
}

?>