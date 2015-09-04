<?php
/**
 * StorageManager.php
 *
 * Copyright 2003-2013, Moxiecode Systems AB, All rights reserved.
 */

/**
 * This class handles storage engines and hands out instances for global, user and group storage.
 *
 * @package MOXMAN_Storage
 */
class MOXMAN_Storage_StorageManager {
	/** @ignore */
	private $config, $user, $storageClassName;

	/**
	 * Constructs a new storage manager instance.
	 *
	 * @param MOXMAN_Util_Config $config Config instance to use for the storage.
	 * @param MOXMAN_Auth_User $user User to get name or groups from.
	 */
	public function __construct($config, $user) {
		$this->config = $config;
		$this->user = $user;
		$this->storageClassName = $this->config->get("storage.engine", "json");
	}

	/**
	 * Sets the storage class name to be created and used.
	 *
	 * @param string $className Storage class name to use.
	 */
	public function setStorageClass($className) {
		$this->storageClassName = $className;
	}

	/**
	 * Returns the storage class name.
	 *
	 * @return String Storage class name.
	 */
	public function getStorageClass() {
		return $this->storageClassName;
	}

	/**
	 * Returns a storage instance for the global scope. Global data is shared between all users.
	 *
	 * @return MOXMAN_Storage_IStorage Storage instance for global data.
	 */
	public function getGlobalStorage() {
		$this->loadInternal();

		$storageClass = $this->storageClassName;
		$storage = new $storageClass();
		$storage->initialize($this->config, MOXMAN_Storage_IStorage::TYPE_GLOBAL, "");

		return $storage;
	}

	/**
	 * Returns a storage instance for the user scope. Items stored in this instance will only be available for the specific user.
	 *
	 * @return MOXMAN_Storage_IStorage Storage instance for global data.
	 */
	public function getUserStorage() {
		$this->loadInternal();

		$storageClass = $this->storageClassName;
		$storage = new $storageClass();
		$storage->initialize($this->config, MOXMAN_Storage_IStorage::TYPE_USER, $this->user->getName());

		return $storage;
	}

	/**
	 * Returns a storage instance for the group scope. Items stored in this instance will only be available
	 * for the specific group and if the user has access to that group.
	 *
	 * @param string $name Name of the group to get the storage for.
	 * @return MOXMAN_Storage_IStorage Storage instance for global data.
	 */
	public function getGroupStorage($name) {
		$this->loadInternal();

		if (!$this->user->isMemberOf($name)) {
			throw new MOXMAN_Exception("User " . $this->user->getName() . " is not a member of group: " . $name);
		}

		$storageClass = $this->storageClassName;
		$storage = new $storageClass();
		$storage->initialize($this->config, MOXMAN_Storage_IStorage::TYPE_GROUP, $name);

		return $storage;
	}

	// @codeCoverageIgnoreStart

	/** @ignore */
	private function loadInternal() {
		// Load internal engines when used
		switch ($this->storageClassName) {
			case "json":
				$this->storageClassName = "MOXMAN_Storage_JsonStorage";
				break;

			case "mysql":
				$this->storageClassName = "MOXMAN_Storage_MySqlStorage";
				break;

			case "sqlite":
				$this->storageClassName = "MOXMAN_Storage_SqliteStorage";
				break;
		}
	}

	// @codeCoverageIgnoreEnd
}

?>