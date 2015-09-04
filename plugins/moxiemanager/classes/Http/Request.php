<?php
/**
 * Request.php
 *
 * Copyright 2003-2013, Moxiecode Systems AB, All rights reserved.
 */

/**
 * This class wrapps in the HTTP Request and adds various useful options.
 *
 * @package MOXMAN_Http
 */
class MOXMAN_Http_Request {
	/**
	 * Name/value array with query string parameters.
	 *
	 * @var array
	 */
	protected $getParams;

	/**
	 * Name/value array with form post data parameters.
	 *
	 * @var array
	 */
	protected $postParams;

	/**
	 * Array object with URL detials. Like host, protocol, port etc.
	 *
	 * @var array
	 */
	protected $url;

	/**
	 * Name/value array with all HTTP headers.
	 *
	 * @var array
	 */
	protected $headers;

	/**
	 * Constructs a new HTTP Request instance.
	 */
	public function __construct() {
		$this->getParams = $_GET;
		$this->postParams = $_POST;

		// @codeCoverageIgnoreStart
		if (function_exists('getallheaders')) {
			$this->headers = getallheaders();
		} else {
			$this->headers = array();
		}
		// @codeCoverageIgnoreEnd
	}

	/**
	 * Returns the current HTTP method as an uppercase string.
	 *
	 * @return String Upper case HTTP Method like GET, POST etc.
	 */
	public function getMethod() {
		return strtoupper($_SERVER['REQUEST_METHOD']);
	}

	/**
	 * Returns all headers as an array.
	 *
	 * @return Array Name/value array of all headers.
	 */
	public function getHeaders() {
		return $this->headers;
	}

	/**
	 * Returns a specific header by name.
	 *
	 * @param String $name Name of header to get.
	 * @param String $defaultValue Optional default value if the header isn't defined.
	 * @return String Current header value or default value if it wasn't found.
	 */
	public function getHeader($name, $defaultValue = null) {
		if (isset($this->headers[$name])) {
			return $this->headers[$name];
		}

		return $defaultValue;
	}

	/**
	 * Returns an array of file items that got uploaded.
	 *
	 * @codeCoverageIgnore
	 * @return Array Array with the files that got uploaded.
	 */
	public function getFiles() {
		$files = array();

		// Files found then return them as an array
		foreach (array_keys($_FILES) as $name) {
			$files[] = $this->getFile($name);
		}

		return $files;
	}

	/**
	 * Returns a single file object by name or null.
	 *
	 * @codeCoverageIgnore
	 * @param string $name Name of multipart file upload form field.
	 * @return array File array object or null.
	 */
	public function getFile($name) {
		if (isset($_FILES[$name])) {
			$file = $_FILES[$name];

			if (isset($file["name"]) && isset($file["tmp_name"])) {
				if (is_uploaded_file($file["tmp_name"])) {
					return $file;
				}
			}
		}

		$file = null;
		return $file;
	}

	/**
	 * Returns a name/value array with all items merged.
	 *
	 * @return Array Name/value array with get and post merged.
	 */
	public function getAll() {
		$items = array_merge($this->getParams, $this->postParams);

		// Remove the slashes
		// @codeCoverageIgnoreStart
		if (ini_get("magic_quotes_gpc")) {
			foreach ($items as $key => $value) {
				$items[$key] = stripslashes($value);
			}
		}
		// @codeCoverageIgnoreEnd

		return $items;
	}

	/**
	 * Returns the specified item stripped from slashes.
	 *
	 * @param string $name Name of the item to retrive.
	 * @param string $default Default value to return if the specified item wasn't found.
	 * @return String Value of the specified string or an empty string if it wasn't found.
	 */
	public function get($name, $default = "") {
		// Get value from get/post
		if (isset($this->getParams[$name])) {
			$value = $this->getParams[$name];
		} else if (isset($this->postParams[$name])) {
			$value = $this->postParams[$name];
		}

		// Decode value if needed
		// @codeCoverageIgnoreStart
		if (isset($value)) {
			if (ini_get("magic_quotes_gpc")) {
				$value = stripslashes($value);
			}

			return $value;
		}
		// @codeCoverageIgnoreEnd

		// Return default value
		return $default;
	}

	/**
	 * Returns true/false if the request has the specified item name or not.
	 *
	 * @param string $name Name if the item to check for.
	 * @return Boolean True/false if the item exists or not.
	 */
	public function has($name) {
		return isset($this->getParams[$name]) || isset($this->postParams[$name]);
	}

	/**
	 * Returns the currently requested page as an URL name/value array.
	 *
	 * @return Array Name/value array with URL details.
	 */
	public function getUrl() {
		if (!$this->url) {
			$url = 'http';

			if (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") {
				$url .= "s";
			}

			$url .= "://" . $_SERVER["SERVER_NAME"];

			if ($_SERVER["SERVER_PORT"] !== "80") {
				$url .= ":" . $_SERVER["SERVER_PORT"];
			}

			$url .= $_SERVER["REQUEST_URI"];
			$this->url = parse_url($url);

			if (isset($this->url["port"]) && $this->url["port"] === 80) {
				unset($this->url["port"]);
			}
		}

		return $this->url;
	}
}

?>