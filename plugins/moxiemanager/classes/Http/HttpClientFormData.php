<?php
/**
 * HttpClientFormData.php
 *
 * Copyright 2003-2013, Moxiecode Systems AB, All rights reserved.
 */

/**
 * Contains the data to be sent to forms like multipart data.
 *
 * @package MOXMAN_Http
 */
class MOXMAN_Http_HttpClientFormData {
	/** @ignore */
	private $items;

	/**
	 * Constructs a new http client form data instance.
	 */
	public function __construct() {
		$this->items = array();
	}

	/**
	 * Sets a key/value string item to the client form instance.
	 *
	 * @param string $name Name of the form item to set.
	 * @param string $value Value of the form item.
	 * @return Instance to self for chainability.
	 */
	public function put($name, $value) {
		$this->items[$name] = $value;

		return $this;
	}

	/**
	 * Sets a key/value file item.
	 *
	 * @param string $name Name of the field to set.
	 * @param string $path Local file system path to file to send.
	 * @param string $filename File name to send file as.
	 * @param string $mime Mime type to upload file as.
	 * @return Instance to self for chainability.
	 */
	public function putFile($name, $path, $filename = "", $mime = "application/octet-stream") {
		$this->items[$name] = array($path, $filename ? $filename : basename($path), $mime);

		return $this;
	}

	/**
	 * Returns the items inside the form data.
	 *
	 * @return Array Form items.
	 */
	public function getItems() {
		return $this->items;
	}

	/**
	 * Returns true/false if the object has any file data.
	 *
	 * @return boolean true/false if this instance has file data or not.
	 */
	public function hasFileData() {
		$values = array_values($this->items);
		foreach ($values as $value) {
			if (is_array($value)) {
				return true;
			}
		}

		return false;
	}
}

?>