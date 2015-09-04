<?php
/**
 * CommandCollection.php
 *
 * Copyright 2003-2013, Moxiecode Systems AB, All rights reserved.
 */

/**
 * Handles instances of MOXMAN_ICommand. This lets you execute handlers by name.
 *
 * @package MOXMAN
 */
class MOXMAN_CommandCollection {
	/** @ignore */
	private $commands;

	/**
	 * Constructs a new command collection.
	 */
	public function __construct() {
		$this->commands = array();
	}

	/**
	 * Executes the specified command by name.'
	 *
	 * @param string $name Name of the command to execute.
	 * @param Object $params Object with parameters to command.
	 * @return Mixed Object or null if the command wasn't found.
	 */
	public function execute($name, $params) {
		$name = strtolower($name);

		if (isset($this->commands[$name])) {
			$className = $this->commands[$name];
			$commandClassInstance = new $className();

			if ($commandClassInstance instanceof MOXMAN_ICommand) {
				return $commandClassInstance->execute($params);
			} else {
				throw new MOXMAN_Exception("Class for name " . $name . " doesn't implement MOXMAN_ICommand interface.");
			}
		}

		return null;
	}

	/**
	 * Adds commands to class names.
	 *
	 * @param array $map Name/value array with command name to class name map.
	 */
	public function addClasses($map) {
		foreach ($map as $name => $value) {
			$this->commands[strtolower($name)] = $value;
		}
	}
}

?>