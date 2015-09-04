<?php
/**
 * User.php
 *
 * Copyright 2003-2013, Moxiecode Systems AB, All rights reserved.
 */

/**
 * This class reprecents the currently logged in user.
 *
 * @package MOXMAN_Auth
 */
class MOXMAN_Auth_User {
	/** @ignore */
	private $groups, $name, $password, $persistent;

	/**
	 * Constructs a new user instance.
	 */
	public function __construct() {
		$this->groups = array();
		$this->name = "anonymous";
		$this->password = "";
		$this->persistent = false;
	}

	/**
	 * Returns the name of the user.
	 *
	 * @return String Name of the user.
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * Sets the name of the user.
	 *
	 * @param string $name Name of the user.
	 */
	public function setName($name) {
		$this->name = $name;
	}

	/**
	 * Returns the password for the user used when a login action occurs.
	 *
	 * @return String Password for the user.
	 */
	public function getPassword() {
		return $this->password;
	}

	/**
	 * Sets the password for the user.
	 *
	 * @param string $password Password for the user.
	 */
	public function setPassword($password) {
		$this->password = $password;
	}

	/**
	 * Sets true/false is the user is to be persistent on the client side using auth cookies.
	 *
	 * @param boolean $persistent Persistent state.
	 */
	public function setPersistent($persistent) {
		$this->persistent = $persistent;
	}

	/**
	 * Returns true/dalse if the user is persistent or not.
	 *
	 * @return boolean true/false if the user is persitent or not.
	 */
	public function isPersistent() {
		return $this->persistent;
	}

	/**
	 * Returns an array of the groups the users is a member of.
	 *
	 * @return Array Array of the groups the users is a member of.
	 */
	public function getGroups() {
		return $this->groups;
	}

	/**
	 * Return true/false if the specified name is a group that the user is a member of.
	 *
	 * @param string $name Name of the group to check membership on.
	 * @return Boolean true/false if the user if a member of the specified group or not.
	 */
	public function isMemberOf($name) {
		return in_array($name, $this->groups);
	}

	/**
	 * Adds the user to the specified group by name.
	 *
	 * @param string $group Group to join.
	 * @return User instance for chainability.
	 */
	public function joinGroup($group) {
		if (!in_array($group, $this->groups)) {
			$this->groups[] = $group;
		}

		return $this;
	}

	/**
	 * Removes the user from specified group by name.
	 *
	 * @param string $group Group to leave.
	 * @return User instance for chainability.
	 */
	public function leaveGroup($group) {
		if (in_array($group, $this->groups)) {
			array_splice($this->groups, array_search($group, $this->groups), 1);
		}

		return $this;
	}
}

?>