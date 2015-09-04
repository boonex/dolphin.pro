<?php
/**
 * JsonStorage.php
 *
 * Copyright 2003-2013, Moxiecode Systems AB, All rights reserved.
 */

/**
 * This class enables plugins to store key/value properties
 * globally, on a specific group or on the current user.
 *
 * @package MOXMAN_Storage
 */
class MOXMAN_Storage_JsonStorage implements MOXMAN_Storage_IStorage {
	/** @ignore */
	private $config, $type, $name, $storagePath, $data, $loaded;

	/**
	 * Initializes the storage instance.
	 *
	 * @param MOXMAN_Util_Config $config Config instance.
	 * @param int $type Storage type to use, can be any of the type constants.
	 * @param string $name Name of the user/group if those types are used or an empty string.
	 */
	public function initialize($config, $type, $name) {
		$this->config = $config;
		$this->type = $type;
		$this->name = $name;
		$this->storagePath = MOXMAN_Util_PathUtils::combine($config->get("storage.path"), ($name ? $type . "." . $name : $type) . ".json");
	}

	/**
	 * Returns a name/value array of all the properties in the storage.
	 *
	 * @return Array Name/value array of all the properties in the storage.
	 */
	public function getAll() {
		return $this->load();
	}

	/**
	 * Returns a specific property by name.
	 *
	 * @param string $name Name of the property to retrive.
	 * @param string $default Default value to return if the property didn't exist.
	 * @return Property value or default value depending on if the property existed or not.
	 */
	public function get($name, $default = null) {
		$data = $this->load();

		return isset($data->{$name}) ? $data->{$name} : $default;
	}

	/**
	 * Puts the specified property by name into storage.
	 *
	 * @param string $name Name of the property to store.
	 * @param string $value Value of the property to store.
	 * @return Storage instance so you can chain put calls.
	 */
	public function put($name, $value) {
		$this->load();
		$this->data->{$name} = $value;
		return $this->save();
	}

	/**
	 * Removes the specified property by name.
	 *
	 * @param string $name Name of the property to remove.
	 * @return Storage instance so you can chain put calls.
	 */
	public function remove($name) {
		$this->load();
		unset($this->data->{$name});
		return $this->save();
	}

	/** @ignore */
	private function load() {
		if (!$this->loaded) {
			if (file_exists($this->storagePath)) {
				$this->data = MOXMAN_Util_Json::decode(file_get_contents($this->storagePath));
			}

			if (!is_object($this->data)) {
				$this->data = new stdClass();
			}

			$this->loaded = true;
		}

		return $this->data;
	}

	/** @ignore */
	private function save() {
		// @codeCoverageIgnoreStart
		if (!is_writable(dirname($this->storagePath))) {
			return $this;
		}
		// @codeCoverageIgnoreEnd

		$json = MOXMAN_Util_Json::encode($this->data, $this->config->get("general.debug"));

		// If there is no data to store remove the storage file
		if ($json === "{}" && file_exists($this->storagePath)) {
			unlink($this->storagePath);
		} else {
			file_put_contents($this->storagePath, $json);
		}

		return $this;
	}
}

?>