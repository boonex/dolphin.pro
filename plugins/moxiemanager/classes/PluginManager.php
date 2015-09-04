<?php
/**
 * PluginManager.php
 *
 * Copyright 2003-2013, Moxiecode Systems AB, All rights reserved.
 */

/**
 * This class handles plugin event delegation and rpc calls.
 *
 * @package MOXMAN
 */
class MOXMAN_PluginManager {
	/** @ignore */
	private $plugins, $unInitialized;

	/**
	 * Constructs a new PluginManager instance.
	 */
	public function __construct() {
		$this->plugins = array();
		$this->unInitialized = array();
	}

	/**
	 * Adds a plugin instance by name to the manager.
	 *
	 * @param string $name Name of the plugin to add.
	 * @param MOXMAN_IPlugin $plugin Plugin instance to add.
	 */
	public function add($name, MOXMAN_IPlugin $plugin) {
		if ($plugin instanceof MOXMAN_Auth_IAuthenticator) {
			MOXMAN::getAuthManager()->add($name, $plugin);
		}

		$this->plugins[$name] = $plugin;
		$this->unInitialized[] = $name;
	}

	/**
	 * Returns a plugin instance by name.
	 *
	 * @param string $name Name of the plugin instance to retrive.
	 * @return MOXMAN_IPlugin Plugin instance or null if it wasn't found.
	 */
	public function get($name) {
		if ($this->has($name)) {
			return $this->plugins[$name];
		}

		return null;
	}

	/**
	 * Returns a plugin instance by name.
	 *
	 * @param string $name Name of the plugin instance to retrive.
	 * @return Boolean true/false if the plugin exists or not.
	 */
	public function has($name) {
		return isset($this->plugins[$name]);
	}

	/**
	 * Removes the specified plugin by name.
	 *
	 * @param string $name Name of the plugin to remove.
	 * @return MOXMAN_IPlugin Plugin instance that got removed or null if it wasn't found.
	 */
	public function remove($name) {
		$plugin = null;

		if (isset($this->plugins[$name])) {
			$plugin = $this->plugins[$name];
			unset($this->plugins[$name]);
		}

		return $plugin;
	}

	/**
	 * Returns all plugins in plugin manager as an name/value array.
	 *
	 * @return Array Name/value array with plugin instances.
	 */
	public function getAll() {
		return $this->plugins;
	}

	/**
	 * Initializes all plugins.
	 */
	public function initAll() {
		foreach ($this->unInitialized as $pluginName) {
			$this->plugins[$pluginName]->init();
		}

		$this->unInitialized = array();
	}
}
?>