<?php
/**
 * IStorage.php
 *
 * Copyright 2003-2013, Moxiecode Systems AB, All rights reserved.
 */

/**
 * This interface enables plugins to store key/value properties
 * globally, on a specific group or on the current user.
 *
 * @package MOXMAN_Storage
 */
interface MOXMAN_Storage_IStorage {
	/**
	 * User type constant. Used when storing user specific properties.
	 */
	const TYPE_USER = "user";

	/**
	 * Group type constant. Used when storing group specific properties.
	 */
	const TYPE_GROUP = "group";

	/**
	 * Global type constant. Used when storing global properties.
	 */
	const TYPE_GLOBAL = "global";

	/**
	 * Initializes the storage instance.
	 *
	 * @param MOXMAN_Util_Config $config Config instance.
	 * @param int $type Storage type to use, can be any of the type constants.
	 * @param string $name Name of the user/group if those types are used or an empty string.
	 */
	public function initialize($config, $type, $name);

	/**
	 * Returns a name/value array of all the properties in the storage.
	 *
	 * @return Array Name/value array of all the properties in the storage.
	 */
	public function getAll();

	/**
	 * Returns a specific property by name.
	 *
	 * @param string $name Name of the property to retrive.
	 * @param string $default Default value to return if the property didn't exist.
	 * @return Property value or default value depending on if the property existed or not.
	 */
	public function get($name, $default = null);

	/**
	 * Puts the specified property by name into storage.
	 *
	 * @param string $name Name of the property to store.
	 * @param string $value Value of the property to store.
	 * @return Storage instance so you can chain put calls.
	 */
	public function put($name, $value);

	/**
	 * Removes the specified property by name.
	 *
	 * @param string $name Name of the property to remove.
	 * @return Storage instance so you can chain put calls.
	 */
	public function remove($name);
}

?>