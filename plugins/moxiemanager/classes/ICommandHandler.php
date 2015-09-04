<?php
/**
 * ICommandHandler.php
 *
 * Copyright 2003-2013, Moxiecode Systems AB, All rights reserved.
 */

/**
 * Classes implementing this interface can execute commands by name and return the result. This is normally
 * implemented by plugins but can be external classes as well.
 *
 * @package MOXMAN
 */
interface MOXMAN_ICommandHandler {
	/**
	 * Executes a specific command by name.
	 *
	 * @param string $name Name of the command to execute.
	 * @param object $params Object with parameters for the command.
	 * @return object Result object or null if the command wasn't handled.
	 */
	public function execute($name, $params);
}

?>