<?php
/**
 * MySqlStorage.php
 *
 * Copyright 2003-2013, Moxiecode Systems AB, All rights reserved.
 */

/**
 * This class enables plugins to store key/value properties
 * globally, on a specific group or on the current user.
 *
 * @package MOXMAN_Storage
 */
class MOXMAN_Storage_MySqlStorage implements MOXMAN_Storage_IStorage {
	/** @ignore */
	private $config, $type, $name, $pdo;

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

		$this->pdo = new MOXMAN_Util_Pdo(
			$config->get("sql.connection"),
			$config->get("sql.username"),
			$config->get("sql.password"),
			array(),
			$config->get("sql.table_prefix")
		);

		$this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	}

	/**
	 * Returns a name/value array of all the properties in the storage.
	 *
	 * @return Array Name/value array of all the properties in the storage.
	 */
	public function getAll() {
		$result = array();
		$pdo = $this->pdo;

		switch ($this->type) {
			case self::TYPE_GLOBAL:
				$statement = $pdo->q("SELECT pr_name, pr_value FROM moxman_property WHERE pr_user IS NULL AND pr_group IS NULL");
				break;

			case self::TYPE_GROUP:
				$statement = $pdo->q(
					"SELECT pr_name, pr_value FROM moxman_property WHERE pr_group = ?",
					$this->name
				);
				break;

			case self::TYPE_USER:
				$statement = $pdo->q(
					"SELECT pr_name, pr_value FROM moxman_property WHERE pr_user = ?",
					$this->name
				);
				break;
		}

		foreach ($statement as $row) {
			$result[$row["pr_name"]] = $row["pr_value"];
		}

		return $result;
	}

	/**
	 * Returns a specific property by name.
	 *
	 * @param string $name Name of the property to retrive.
	 * @param string $default Default value to return if the property didn't exist.
	 * @return Property value or default value depending on if the property existed or not.
	 */
	public function get($name, $default = null) {
		$pdo = $this->pdo;

		switch ($this->type) {
			case self::TYPE_GLOBAL:
				$statement = $pdo->q("SELECT pr_name, pr_value FROM moxman_property WHERE pr_user IS NULL AND pr_group IS NULL AND pr_name = ?", $name);
				break;

			case self::TYPE_GROUP:
				$statement = $pdo->q(
					"SELECT pr_name, pr_value FROM moxman_property WHERE pr_group = ? AND pr_name = ?",
					$this->name,
					$name
				);
				break;

			case self::TYPE_USER:
				$statement = $pdo->q(
					"SELECT pr_name, pr_value FROM moxman_property WHERE pr_user = ? AND pr_name = ?",
					$this->name,
					$name
				);
				break;
		}

		$result = $statement->fetch();

		return isset($result["pr_value"]) ? $result["pr_value"] : $default;
	}

	/**
	 * Puts the specified property by name into storage.
	 *
	 * @param string $name Name of the property to store.
	 * @param string $value Value of the property to store.
	 * @return Storage instance so you can chain put calls.
	 */
	public function put($name, $value) {
		$pdo = $this->pdo;

		switch ($this->type) {
			case self::TYPE_GLOBAL:
				if ($pdo->i('SELECT COUNT(pr_id) FROM moxman_property WHERE pr_user IS NULL AND pr_group IS NULL AND pr_name = ?', $name) > 0) {
					$pdo->q("UPDATE moxman_property SET pr_value = :value, pr_modificationdate = NOW() WHERE pr_user IS NULL AND pr_group IS NULL AND pr_name = :name", array(
						"name" => $name,
						"value" => $value
					));
				} else {
					$pdo->q("INSERT INTO moxman_property(pr_name, pr_value, pr_creationdate, pr_modificationdate) VALUES(:name, :value, NOW(), NOW())", array(
						"name" => $name,
						"value" => $value
					));
				}
				break;

			case self::TYPE_GROUP:
				if ($pdo->i('SELECT COUNT(pr_id) FROM moxman_property WHERE pr_group = ? AND pr_name = ?', $this->name, $name) > 0) {
					$pdo->q("UPDATE moxman_property SET pr_value = :value, pr_modificationdate = NOW() WHERE pr_group = :grname AND pr_name = :name", array(
						"grname" => $this->name,
						"name" => $name,
						"value" => $value
					));
				} else {
					$pdo->q("INSERT INTO moxman_property(pr_group, pr_name, pr_value, pr_creationdate, pr_modificationdate) VALUES(:grname, :name, :value, NOW(), NOW())", array(
						"grname" => $this->name,
						"name" => $name,
						"value" => $value
					));
				}
				break;

			case self::TYPE_USER:
				if ($pdo->i('SELECT COUNT(pr_id) FROM moxman_property WHERE pr_user = ? AND pr_name = ?', $this->name, $name) > 0) {
					$pdo->q("UPDATE moxman_property SET pr_value = :value, pr_modificationdate = NOW() WHERE pr_user = :usname AND pr_name = :name", array(
						"usname" => $this->name,
						"name" => $name,
						"value" => $value
					));
				} else {
					$pdo->q("INSERT INTO moxman_property(pr_user, pr_name, pr_value, pr_creationdate, pr_modificationdate) VALUES(:usname, :name, :value, NOW(), NOW())", array(
						"usname" => $this->name,
						"name" => $name,
						"value" => $value
					));
				}
				break;
		}

		return $this;
	}

	/**
	 * Removes the specified property by name.
	 *
	 * @param string $name Name of the property to remove.
	 * @return Storage instance so you can chain put calls.
	 */
	public function remove($name) {
		$pdo = $this->pdo;

		switch ($this->type) {
			case self::TYPE_GLOBAL:
				$pdo->q("DELETE FROM moxman_property WHERE pr_user IS NULL and pr_group IS NULL AND pr_name = ?", $name);
				break;

			case self::TYPE_GROUP:
				$pdo->q(
					"DELETE FROM moxman_property WHERE pr_group = ? AND pr_name = ?",
					$this->name,
					$name
					);
				break;

			case self::TYPE_USER:
				$pdo->q(
					"DELETE FROM moxman_property WHERE pr_user = ? AND pr_name = ?",
					$this->name,
					$name
					);
				break;
		}

		return $this;
	}
}

?>